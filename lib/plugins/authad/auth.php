<?php

use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Utf8\Clean;
use dokuwiki\Utf8\PhpString;
use dokuwiki\Utf8\Sort;
use dokuwiki\Logger;

/**
 * Active Directory authentication backend for DokuWiki
 *
 * This makes authentication with a Active Directory server much easier
 * than when using the normal LDAP backend by utilizing the adLDAP library
 *
 * Usage:
 *   Set DokuWiki's local.protected.php auth setting to read
 *
 *   $conf['authtype']       = 'authad';
 *
 *   $conf['plugin']['authad']['account_suffix']     = '@my.domain.org';
 *   $conf['plugin']['authad']['base_dn']            = 'DC=my,DC=domain,DC=org';
 *   $conf['plugin']['authad']['domain_controllers'] = 'srv1.domain.org,srv2.domain.org';
 *
 *   //optional:
 *   $conf['plugin']['authad']['sso']                = 1;
 *   $conf['plugin']['authad']['admin_username']     = 'root';
 *   $conf['plugin']['authad']['admin_password']     = 'pass';
 *   $conf['plugin']['authad']['real_primarygroup']  = 1;
 *   $conf['plugin']['authad']['use_ssl']            = 1;
 *   $conf['plugin']['authad']['use_tls']            = 1;
 *   $conf['plugin']['authad']['debug']              = 1;
 *   // warn user about expiring password this many days in advance:
 *   $conf['plugin']['authad']['expirywarn']         = 5;
 *
 *   // get additional information to the userinfo array
 *   // add a list of comma separated ldap contact fields.
 *   $conf['plugin']['authad']['additional'] = 'field1,field2';
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  James Van Lommel <jamesvl@gmail.com>
 * @link    http://www.nosq.com/blog/2005/08/ldap-activedirectory-and-dokuwiki/
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Jan Schumann <js@schumann-it.com>
 */
class auth_plugin_authad extends AuthPlugin
{
    /**
     * @var array hold connection data for a specific AD domain
     */
    protected $opts = [];

    /**
     * @var array open connections for each AD domain, as adLDAP objects
     */
    protected $adldap = [];

    /**
     * @var bool message state
     */
    protected $msgshown = false;

    /**
     * @var array user listing cache
     */
    protected $users = [];

    /**
     * @var array filter patterns for listing users
     */
    protected $pattern = [];

    protected $grpsusers = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        global $INPUT;
        parent::__construct();

        require_once(DOKU_PLUGIN . 'authad/adLDAP/adLDAP.php');
        require_once(DOKU_PLUGIN . 'authad/adLDAP/classes/adLDAPUtils.php');

        // we load the config early to modify it a bit here
        $this->loadConfig();

        // additional information fields
        if (isset($this->conf['additional'])) {
            $this->conf['additional'] = str_replace(' ', '', $this->conf['additional']);
            $this->conf['additional'] = explode(',', $this->conf['additional']);
        } else $this->conf['additional'] = [];

        // ldap extension is needed
        if (!function_exists('ldap_connect')) {
            if ($this->conf['debug'])
                msg("AD Auth: PHP LDAP extension not found.", -1);
            $this->success = false;
            return;
        }

        // Prepare SSO
        if (!empty($INPUT->server->str('REMOTE_USER'))) {
            // make sure the right encoding is used
            if ($this->getConf('sso_charset')) {
                $INPUT->server->set(
                    'REMOTE_USER',
                    iconv($this->getConf('sso_charset'), 'UTF-8', $INPUT->server->str('REMOTE_USER'))
                );
            } elseif (!Clean::isUtf8($INPUT->server->str('REMOTE_USER'))) {
                $INPUT->server->set('REMOTE_USER', utf8_encode($INPUT->server->str('REMOTE_USER')));
            }

            // trust the incoming user
            if ($this->conf['sso']) {
                $INPUT->server->set('REMOTE_USER', $this->cleanUser($INPUT->server->str('REMOTE_USER')));

                // we need to simulate a login
                if (empty($_COOKIE[DOKU_COOKIE])) {
                    $INPUT->set('u', $INPUT->server->str('REMOTE_USER'));
                    $INPUT->set('p', 'sso_only');
                }
            }
        }

        // other can do's are changed in $this->_loadServerConfig() base on domain setup
        $this->cando['modName'] = (bool)$this->conf['update_name'];
        $this->cando['modMail'] = (bool)$this->conf['update_mail'];
        $this->cando['getUserCount'] = true;
    }

    /**
     * Load domain config on capability check
     *
     * @param string $cap
     * @return bool
     */
    public function canDo($cap)
    {
        global $INPUT;
        //capabilities depend on config, which may change depending on domain
        $domain = $this->getUserDomain($INPUT->server->str('REMOTE_USER'));
        $this->loadServerConfig($domain);
        return parent::canDo($cap);
    }

    /**
     * Check user+password [required auth function]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct by trying to bind
     * to the LDAP server
     *
     * @author  James Van Lommel <james@nosq.com>
     * @param string $user
     * @param string $pass
     * @return  bool
     */
    public function checkPass($user, $pass)
    {
        global $INPUT;
        if (
            $INPUT->server->str('REMOTE_USER') == $user &&
            $this->conf['sso']
        ) return true;

        $adldap = $this->initAdLdap($this->getUserDomain($user));
        if (!$adldap) return false;

        try {
            return $adldap->authenticate($this->getUserName($user), $pass);
        } catch (adLDAPException $e) {
            // shouldn't really happen
            return false;
        }
    }

    /**
     * Return user info [required auth function]
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name    string  full name of the user
     * mail    string  email address of the user
     * grps    array   list of groups the user is in
     *
     * This AD specific function returns the following
     * addional fields:
     *
     * dn         string    distinguished name (DN)
     * uid        string    samaccountname
     * lastpwd    int       timestamp of the date when the password was set
     * expires    true      if the password expires
     * expiresin  int       seconds until the password expires
     * any fields specified in the 'additional' config option
     *
     * @author  James Van Lommel <james@nosq.com>
     * @param string $user
     * @param bool $requireGroups (optional) - ignored, groups are always supplied by this plugin
     * @return array|false
     */
    public function getUserData($user, $requireGroups = true)
    {
        global $conf;
        global $lang;
        global $ID;
        global $INPUT;
        $adldap = $this->initAdLdap($this->getUserDomain($user));
        if (!$adldap) return false;
        if ($user == '') return false;

        $fields = ['mail', 'displayname', 'samaccountname', 'lastpwd', 'pwdlastset', 'useraccountcontrol'];

        // add additional fields to read
        $fields = array_merge($fields, $this->conf['additional']);
        $fields = array_unique($fields);
        $fields = array_filter($fields);

        //get info for given user
        $result = $adldap->user()->info($this->getUserName($user), $fields);
        if ($result == false) {
            return false;
        }

        //general user info
        $info = [];
        $info['name'] = $result[0]['displayname'][0];
        $info['mail'] = $result[0]['mail'][0];
        $info['uid']  = $result[0]['samaccountname'][0];
        $info['dn']   = $result[0]['dn'];
        //last password set (Windows counts from January 1st 1601)
        $info['lastpwd'] = $result[0]['pwdlastset'][0] / 10_000_000 - 11_644_473_600;
        //will it expire?
        $info['expires'] = !($result[0]['useraccountcontrol'][0] & 0x10000); //ADS_UF_DONT_EXPIRE_PASSWD

        // additional information
        foreach ($this->conf['additional'] as $field) {
            if (isset($result[0][strtolower($field)])) {
                $info[$field] = $result[0][strtolower($field)][0];
            }
        }

        // handle ActiveDirectory memberOf
        $info['grps'] = $adldap->user()->groups($this->getUserName($user), (bool) $this->opts['recursive_groups']);

        if (is_array($info['grps'])) {
            foreach ($info['grps'] as $ndx => $group) {
                $info['grps'][$ndx] = $this->cleanGroup($group);
            }
        } else {
            $info['grps'] = [];
        }

        // always add the default group to the list of groups
        if (!in_array($conf['defaultgroup'], $info['grps'])) {
            $info['grps'][] = $conf['defaultgroup'];
        }

        // add the user's domain to the groups
        $domain = $this->getUserDomain($user);
        if ($domain && !in_array("domain-$domain", $info['grps'])) {
            $info['grps'][] = $this->cleanGroup("domain-$domain");
        }

        // check expiry time
        if ($info['expires'] && $this->conf['expirywarn']) {
            try {
                $expiry = $adldap->user()->passwordExpiry($user);
                if (is_array($expiry)) {
                    $info['expiresat'] = $expiry['expiryts'];
                    $info['expiresin'] = round(($info['expiresat'] - time()) / (24 * 60 * 60));

                    // if this is the current user, warn him (once per request only)
                    if (
                        ($INPUT->server->str('REMOTE_USER') == $user) &&
                        ($info['expiresin'] <= $this->conf['expirywarn']) &&
                        !$this->msgshown
                    ) {
                        $msg = sprintf($this->getLang('authpwdexpire'), $info['expiresin']);
                        if ($this->canDo('modPass')) {
                            $url = wl($ID, ['do' => 'profile']);
                            $msg .= ' <a href="' . $url . '">' . $lang['btn_profile'] . '</a>';
                        }
                        msg($msg);
                        $this->msgshown = true;
                    }
                }
            } catch (adLDAPException $e) {
                // ignore. should usually not happen
            }
        }

        return $info;
    }

    /**
     * Make AD group names usable by DokuWiki.
     *
     * Removes backslashes ('\'), pound signs ('#'), and converts spaces to underscores.
     *
     * @author  James Van Lommel (jamesvl@gmail.com)
     * @param string $group
     * @return string
     */
    public function cleanGroup($group)
    {
        $group = str_replace('\\', '', $group);
        $group = str_replace('#', '', $group);
        $group = preg_replace('[\s]', '_', $group);
        $group = PhpString::strtolower(trim($group));
        return $group;
    }

    /**
     * Sanitize user names
     *
     * Normalizes domain parts, does not modify the user name itself (unlike cleanGroup)
     *
     * @author Andreas Gohr <gohr@cosmocode.de>
     * @param string $user
     * @return string
     */
    public function cleanUser($user)
    {
        $domain = '';

        // get NTLM or Kerberos domain part
        [$dom, $user] = sexplode('\\', $user, 2, '');
        if (!$user) $user = $dom;
        if ($dom) $domain = $dom;
        [$user, $dom] = sexplode('@', $user, 2, '');
        if ($dom) $domain = $dom;

        // clean up both
        $domain = PhpString::strtolower(trim($domain));
        $user   = PhpString::strtolower(trim($user));

        // is this a known, valid domain or do we work without account suffix? if not discard
        if (
            (!isset($this->conf[$domain]) || !is_array($this->conf[$domain])) &&
            $this->conf['account_suffix'] !== ''
        ) {
            $domain = '';
        }

        // reattach domain
        if ($domain) $user = "$user@$domain";
        return $user;
    }

    /**
     * Most values in LDAP are case-insensitive
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return false;
    }

    /**
     * Create a Search-String useable by adLDAPUsers::all($includeDescription = false, $search = "*", $sorted = true)
     *
     * @param array $filter
     * @return string
     */
    protected function constructSearchString($filter)
    {
        if (!$filter) {
            return '*';
        }
        $adldapUtils = new adLDAPUtils($this->initAdLdap(null));
        $result = '*';
        if (isset($filter['name'])) {
            $result .= ')(displayname=*' . $adldapUtils->ldapSlashes($filter['name']) . '*';
            unset($filter['name']);
        }

        if (isset($filter['user'])) {
            $result .= ')(samAccountName=*' . $adldapUtils->ldapSlashes($filter['user']) . '*';
            unset($filter['user']);
        }

        if (isset($filter['mail'])) {
            $result .= ')(mail=*' . $adldapUtils->ldapSlashes($filter['mail']) . '*';
            unset($filter['mail']);
        }
        return $result;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     *
     * @param array $filter  $filter array of field/pattern pairs, empty array for no filter
     * @return int number of users
     */
    public function getUserCount($filter = [])
    {
        $adldap = $this->initAdLdap(null);
        if (!$adldap) {
            Logger::debug("authad/auth.php getUserCount(): _adldap not set.");
            return -1;
        }
        if ($filter == []) {
            $result = $adldap->user()->all();
        } else {
            $searchString = $this->constructSearchString($filter);
            $result = $adldap->user()->all(false, $searchString);
            if (isset($filter['grps'])) {
                $this->users = array_fill_keys($result, false);
                /** @var admin_plugin_usermanager $usermanager */
                $usermanager = plugin_load("admin", "usermanager", false);
                $usermanager->setLastdisabled(true);
                if (!isset($this->grpsusers[$this->filterToString($filter)])) {
                    $this->fillGroupUserArray($filter, $usermanager->getStart() + 3 * $usermanager->getPagesize());
                } elseif (
                    count($this->grpsusers[$this->filterToString($filter)]) <
                    $usermanager->getStart() + 3 * $usermanager->getPagesize()
                ) {
                    $this->fillGroupUserArray(
                        $filter,
                        $usermanager->getStart() +
                        3 * $usermanager->getPagesize() -
                        count($this->grpsusers[$this->filterToString($filter)])
                    );
                }
                $result = $this->grpsusers[$this->filterToString($filter)];
            } else {
                /** @var admin_plugin_usermanager $usermanager */
                $usermanager = plugin_load("admin", "usermanager", false);
                $usermanager->setLastdisabled(false);
            }
        }

        if (!$result) {
            return 0;
        }
        return count($result);
    }

    /**
     *
     * create a unique string for each filter used with a group
     *
     * @param array $filter
     * @return string
     */
    protected function filterToString($filter)
    {
        $result = '';
        if (isset($filter['user'])) {
            $result .= 'user-' . $filter['user'];
        }
        if (isset($filter['name'])) {
            $result .= 'name-' . $filter['name'];
        }
        if (isset($filter['mail'])) {
            $result .= 'mail-' . $filter['mail'];
        }
        if (isset($filter['grps'])) {
            $result .= 'grps-' . $filter['grps'];
        }
        return $result;
    }

    /**
     * Create an array of $numberOfAdds users passing a certain $filter, including belonging
     * to a certain group and save them to a object-wide array. If the array
     * already exists try to add $numberOfAdds further users to it.
     *
     * @param array $filter
     * @param int $numberOfAdds additional number of users requested
     * @return int number of Users actually add to Array
     */
    protected function fillGroupUserArray($filter, $numberOfAdds)
    {
        if (isset($this->grpsusers[$this->filterToString($filter)])) {
            $actualstart = count($this->grpsusers[$this->filterToString($filter)]);
        } else {
            $this->grpsusers[$this->filterToString($filter)] = [];
            $actualstart = 0;
        }

        $i = 0;
        $count = 0;
        $this->constructPattern($filter);
        foreach ($this->users as $user => &$info) {
            if ($i++ < $actualstart) {
                continue;
            }
            if ($info === false) {
                $info = $this->getUserData($user);
            }
            if ($this->filter($user, $info)) {
                $this->grpsusers[$this->filterToString($filter)][$user] = $info;
                if (($numberOfAdds > 0) && (++$count >= $numberOfAdds)) break;
            }
        }
        return $count;
    }

    /**
     * Bulk retrieval of user data
     *
     * @author  Dominik Eckelmann <dokuwiki@cosmocode.de>
     *
     * @param   int $start index of first user to be returned
     * @param   int $limit max number of users to be returned
     * @param   array $filter array of field/pattern pairs, null for no filter
     * @return array userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = 0, $filter = [])
    {
        $adldap = $this->initAdLdap(null);
        if (!$adldap) return [];

        //if (!$this->users) {
            //get info for given user
            $result = $adldap->user()->all(false, $this->constructSearchString($filter));
            if (!$result) return [];
            $this->users = array_fill_keys($result, false);
        //}

        $i     = 0;
        $count = 0;
        $result = [];

        if (!isset($filter['grps'])) {
            /** @var admin_plugin_usermanager $usermanager */
            $usermanager = plugin_load("admin", "usermanager", false);
            $usermanager->setLastdisabled(false);
            $this->constructPattern($filter);
            foreach ($this->users as $user => &$info) {
                if ($i++ < $start) {
                    continue;
                }
                if ($info === false) {
                    $info = $this->getUserData($user);
                }
                $result[$user] = $info;
                if (($limit > 0) && (++$count >= $limit)) break;
            }
        } else {
            /** @var admin_plugin_usermanager $usermanager */
            $usermanager = plugin_load("admin", "usermanager", false);
            $usermanager->setLastdisabled(true);
            if (
                !isset($this->grpsusers[$this->filterToString($filter)]) ||
                count($this->grpsusers[$this->filterToString($filter)]) < ($start + $limit)
            ) {
                if (!isset($this->grpsusers[$this->filterToString($filter)])) {
                    $this->grpsusers[$this->filterToString($filter)] = [];
                }

                $this->fillGroupUserArray(
                    $filter,
                    $start + $limit - count($this->grpsusers[$this->filterToString($filter)]) + 1
                );
            }
            if (!$this->grpsusers[$this->filterToString($filter)]) return [];
            foreach ($this->grpsusers[$this->filterToString($filter)] as $user => &$info) {
                if ($i++ < $start) {
                    continue;
                }
                $result[$user] = $info;
                if (($limit > 0) && (++$count >= $limit)) break;
            }
        }
        return $result;
    }

    /**
     * Modify user data
     *
     * @param   string $user      nick of the user to be changed
     * @param   array  $changes   array of field/value pairs to be changed
     * @return  bool
     */
    public function modifyUser($user, $changes)
    {
        $return = true;
        $adldap = $this->initAdLdap($this->getUserDomain($user));
        if (!$adldap) {
            msg($this->getLang('connectfail'), -1);
            return false;
        }

        // password changing
        if (isset($changes['pass'])) {
            try {
                $return = $adldap->user()->password($this->getUserName($user), $changes['pass']);
            } catch (adLDAPException $e) {
                if ($this->conf['debug']) msg('AD Auth: ' . $e->getMessage(), -1);
                $return = false;
            }
            if (!$return) msg($this->getLang('passchangefail'), -1);
        }

        // changing user data
        $adchanges = [];
        if (isset($changes['name'])) {
            // get first and last name
            $parts                     = explode(' ', $changes['name']);
            $adchanges['surname']      = array_pop($parts);
            $adchanges['firstname']    = implode(' ', $parts);
            $adchanges['display_name'] = $changes['name'];
        }
        if (isset($changes['mail'])) {
            $adchanges['email'] = $changes['mail'];
        }
        if ($adchanges !== []) {
            try {
                $return &= $adldap->user()->modify($this->getUserName($user), $adchanges);
            } catch (adLDAPException $e) {
                if ($this->conf['debug']) msg('AD Auth: ' . $e->getMessage(), -1);
                $return = false;
            }
            if (!$return) msg($this->getLang('userchangefail'), -1);
        }

        return $return;
    }

    /**
     * Initialize the AdLDAP library and connect to the server
     *
     * When you pass null as domain, it will reuse any existing domain.
     * Eg. the one of the logged in user. It falls back to the default
     * domain if no current one is available.
     *
     * @param string|null $domain The AD domain to use
     * @return adLDAP|bool true if a connection was established
     */
    protected function initAdLdap($domain)
    {
        if (is_null($domain) && is_array($this->opts)) {
            $domain = $this->opts['domain'];
        }

        $this->opts = $this->loadServerConfig((string) $domain);
        if (isset($this->adldap[$domain])) return $this->adldap[$domain];

        // connect
        try {
            $this->adldap[$domain] = new adLDAP($this->opts);
            return $this->adldap[$domain];
        } catch (Exception $e) {
            if ($this->conf['debug']) {
                msg('AD Auth: ' . $e->getMessage(), -1);
            }
            $this->success         = false;
            $this->adldap[$domain] = null;
        }
        return false;
    }

    /**
     * Get the domain part from a user
     *
     * @param string $user
     * @return string
     */
    public function getUserDomain($user)
    {
        [, $domain] = sexplode('@', $user, 2, '');
        return $domain;
    }

    /**
     * Get the user part from a user
     *
     * When an account suffix is set, we strip the domain part from the user
     *
     * @param string $user
     * @return string
     */
    public function getUserName($user)
    {
        if ($this->conf['account_suffix'] !== '') {
            [$user] = explode('@', $user, 2);
        }
        return $user;
    }

    /**
     * Fetch the configuration for the given AD domain
     *
     * @param string $domain current AD domain
     * @return array
     */
    protected function loadServerConfig($domain)
    {
        // prepare adLDAP standard configuration
        $opts = $this->conf;

        $opts['domain'] = $domain;

        // add possible domain specific configuration
        if ($domain && is_array($this->conf[$domain] ?? '')) foreach ($this->conf[$domain] as $key => $val) {
            $opts[$key] = $val;
        }

        // handle multiple AD servers
        $opts['domain_controllers'] = explode(',', $opts['domain_controllers']);
        $opts['domain_controllers'] = array_map('trim', $opts['domain_controllers']);
        $opts['domain_controllers'] = array_filter($opts['domain_controllers']);

        // compatibility with old option name
        if (empty($opts['admin_username']) && !empty($opts['ad_username'])) {
            $opts['admin_username'] = $opts['ad_username'];
        }
        if (empty($opts['admin_password']) && !empty($opts['ad_password'])) {
            $opts['admin_password'] = $opts['ad_password'];
        }
        $opts['admin_password'] = conf_decodeString($opts['admin_password']); // deobfuscate

        // we can change the password if SSL is set
        if ($opts['update_pass'] && ($opts['use_ssl'] || $opts['use_tls'])) {
            $this->cando['modPass'] = true;
        } else {
            $this->cando['modPass'] = false;
        }

        // adLDAP expects empty user/pass as NULL, we're less strict FS#2781
        if (empty($opts['admin_username'])) $opts['admin_username'] = null;
        if (empty($opts['admin_password'])) $opts['admin_password'] = null;

        // user listing needs admin priviledges
        if (!empty($opts['admin_username']) && !empty($opts['admin_password'])) {
            $this->cando['getUsers'] = true;
        } else {
            $this->cando['getUsers'] = false;
        }

        return $opts;
    }

    /**
     * Returns a list of configured domains
     *
     * The default domain has an empty string as key
     *
     * @return array associative array(key => domain)
     */
    public function getConfiguredDomains()
    {
        $domains = [];
        if (empty($this->conf['account_suffix'])) return $domains; // not configured yet

        // add default domain, using the name from account suffix
        $domains[''] = ltrim($this->conf['account_suffix'], '@');

        // find additional domains
        foreach ($this->conf as $key => $val) {
            if (is_array($val) && isset($val['account_suffix'])) {
                $domains[$key] = ltrim($val['account_suffix'], '@');
            }
        }
        Sort::ksort($domains);

        return $domains;
    }

    /**
     * Check provided user and userinfo for matching patterns
     *
     * The patterns are set up with $this->_constructPattern()
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     * @param string $user
     * @param array  $info
     * @return bool
     */
    protected function filter($user, $info)
    {
        foreach ($this->pattern as $item => $pattern) {
            if ($item == 'user') {
                if (!preg_match($pattern, $user)) return false;
            } elseif ($item == 'grps') {
                if (!count(preg_grep($pattern, $info['grps']))) return false;
            } elseif (!preg_match($pattern, $info[$item])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Create a pattern for $this->_filter()
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     * @param array $filter
     */
    protected function constructPattern($filter)
    {
        $this->pattern = [];
        foreach ($filter as $item => $pattern) {
            $this->pattern[$item] = '/' . str_replace('/', '\/', $pattern) . '/i'; // allow regex characters
        }
    }
}
