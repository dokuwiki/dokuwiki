<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(DOKU_PLUGIN.'authad/adLDAP/adLDAP.php');

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
class auth_plugin_authad extends DokuWiki_Auth_Plugin {

    /**
     * @var array hold connection data for a specific AD domain
     */
    protected $opts = array();

    /**
     * @var array open connections for each AD domain, as adLDAP objects
     */
    protected $adldap = array();

    /**
     * @var bool message state
     */
    protected $msgshown = false;

    /**
     * @var array user listing cache
     */
    protected $users = array();

    /**
     * @var array filter patterns for listing users
     */
    protected $_pattern = array();

    /**
     * Constructor
     */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        // we load the config early to modify it a bit here
        $this->loadConfig();

        // additional information fields
        if(isset($this->conf['additional'])) {
            $this->conf['additional'] = str_replace(' ', '', $this->conf['additional']);
            $this->conf['additional'] = explode(',', $this->conf['additional']);
        } else $this->conf['additional'] = array();

        // ldap extension is needed
        if(!function_exists('ldap_connect')) {
            if($this->conf['debug'])
                msg("AD Auth: PHP LDAP extension not found.", -1);
            $this->success = false;
            return;
        }

        // Prepare SSO
        if(!empty($_SERVER['REMOTE_USER'])) {

            // make sure the right encoding is used
            if($this->getConf('sso_charset')) {
                $_SERVER['REMOTE_USER'] = iconv($this->getConf('sso_charset'), 'UTF-8', $_SERVER['REMOTE_USER']);
            } elseif(!utf8_check($_SERVER['REMOTE_USER'])) {
                $_SERVER['REMOTE_USER'] = utf8_encode($_SERVER['REMOTE_USER']);
            }

            // trust the incoming user
            if($this->conf['sso']) {
                $_SERVER['REMOTE_USER'] = $this->cleanUser($_SERVER['REMOTE_USER']);

                // we need to simulate a login
                if(empty($_COOKIE[DOKU_COOKIE])) {
                    $INPUT->set('u', $_SERVER['REMOTE_USER']);
                    $INPUT->set('p', 'sso_only');
                }
            }
        }

        // other can do's are changed in $this->_loadServerConfig() base on domain setup
        $this->cando['modName'] = true;
        $this->cando['modMail'] = true;
    }

    /**
     * Load domain config on capability check
     *
     * @param string $cap
     * @return bool
     */
    public function canDo($cap) {
        //capabilities depend on config, which may change depending on domain
        $domain = $this->_userDomain($_SERVER['REMOTE_USER']);
        $this->_loadServerConfig($domain);
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
    public function checkPass($user, $pass) {
        if($_SERVER['REMOTE_USER'] &&
            $_SERVER['REMOTE_USER'] == $user &&
            $this->conf['sso']
        ) return true;

        $adldap = $this->_adldap($this->_userDomain($user));
        if(!$adldap) return false;

        return $adldap->authenticate($this->_userName($user), $pass);
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
     * @return array
     */
    public function getUserData($user) {
        global $conf;
        global $lang;
        global $ID;
        $adldap = $this->_adldap($this->_userDomain($user));
        if(!$adldap) return false;

        if($user == '') return array();

        $fields = array('mail', 'displayname', 'samaccountname', 'lastpwd', 'pwdlastset', 'useraccountcontrol');

        // add additional fields to read
        $fields = array_merge($fields, $this->conf['additional']);
        $fields = array_unique($fields);
        $fields = array_filter($fields);

        //get info for given user
        $result = $adldap->user()->info($this->_userName($user), $fields);
        if($result == false){
            return array();
        }

        //general user info
        $info['name'] = $result[0]['displayname'][0];
        $info['mail'] = $result[0]['mail'][0];
        $info['uid']  = $result[0]['samaccountname'][0];
        $info['dn']   = $result[0]['dn'];
        //last password set (Windows counts from January 1st 1601)
        $info['lastpwd'] = $result[0]['pwdlastset'][0] / 10000000 - 11644473600;
        //will it expire?
        $info['expires'] = !($result[0]['useraccountcontrol'][0] & 0x10000); //ADS_UF_DONT_EXPIRE_PASSWD

        // additional information
        foreach($this->conf['additional'] as $field) {
            if(isset($result[0][strtolower($field)])) {
                $info[$field] = $result[0][strtolower($field)][0];
            }
        }

        // handle ActiveDirectory memberOf
        $info['grps'] = $adldap->user()->groups($this->_userName($user),(bool) $this->opts['recursive_groups']);

        if(is_array($info['grps'])) {
            foreach($info['grps'] as $ndx => $group) {
                $info['grps'][$ndx] = $this->cleanGroup($group);
            }
        }

        // always add the default group to the list of groups
        if(!is_array($info['grps']) || !in_array($conf['defaultgroup'], $info['grps'])) {
            $info['grps'][] = $conf['defaultgroup'];
        }

        // add the user's domain to the groups
        $domain = $this->_userDomain($user);
        if($domain && !in_array("domain-$domain", (array) $info['grps'])) {
            $info['grps'][] = $this->cleanGroup("domain-$domain");
        }

        // check expiry time
        if($info['expires'] && $this->conf['expirywarn']){
            $expiry = $adldap->user()->passwordExpiry($user);
            if(is_array($expiry)){
                $info['expiresat'] = $expiry['expiryts'];
                $info['expiresin'] = round(($info['expiresat'] - time())/(24*60*60));

                // if this is the current user, warn him (once per request only)
                if(($_SERVER['REMOTE_USER'] == $user) &&
                    ($info['expiresin'] <= $this->conf['expirywarn']) &&
                    !$this->msgshown
                ) {
                    $msg = sprintf($lang['authpwdexpire'], $info['expiresin']);
                    if($this->canDo('modPass')) {
                        $url = wl($ID, array('do'=> 'profile'));
                        $msg .= ' <a href="'.$url.'">'.$lang['btn_profile'].'</a>';
                    }
                    msg($msg);
                    $this->msgshown = true;
                }
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
    public function cleanGroup($group) {
        $group = str_replace('\\', '', $group);
        $group = str_replace('#', '', $group);
        $group = preg_replace('[\s]', '_', $group);
        $group = utf8_strtolower(trim($group));
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
    public function cleanUser($user) {
        $domain = '';

        // get NTLM or Kerberos domain part
        list($dom, $user) = explode('\\', $user, 2);
        if(!$user) $user = $dom;
        if($dom) $domain = $dom;
        list($user, $dom) = explode('@', $user, 2);
        if($dom) $domain = $dom;

        // clean up both
        $domain = utf8_strtolower(trim($domain));
        $user   = utf8_strtolower(trim($user));

        // is this a known, valid domain? if not discard
        if(!is_array($this->conf[$domain])) {
            $domain = '';
        }

        // reattach domain
        if($domain) $user = "$user@$domain";
        return $user;
    }

    /**
     * Most values in LDAP are case-insensitive
     *
     * @return bool
     */
    public function isCaseSensitive() {
        return false;
    }

    /**
     * Bulk retrieval of user data
     *
     * @author  Dominik Eckelmann <dokuwiki@cosmocode.de>
     * @param   int   $start     index of first user to be returned
     * @param   int   $limit     max number of users to be returned
     * @param   array $filter    array of field/pattern pairs, null for no filter
     * @return  array userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = 0, $filter = array()) {
        $adldap = $this->_adldap(null);
        if(!$adldap) return false;

        if(!$this->users) {
            //get info for given user
            $result = $adldap->user()->all();
            if (!$result) return array();
            $this->users = array_fill_keys($result, false);
        }

        $i     = 0;
        $count = 0;
        $this->_constructPattern($filter);
        $result = array();

        foreach($this->users as $user => &$info) {
            if($i++ < $start) {
                continue;
            }
            if($info === false) {
                $info = $this->getUserData($user);
            }
            if($this->_filter($user, $info)) {
                $result[$user] = $info;
                if(($limit > 0) && (++$count >= $limit)) break;
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
    public function modifyUser($user, $changes) {
        $return = true;
        $adldap = $this->_adldap($this->_userDomain($user));
        if(!$adldap) return false;

        // password changing
        if(isset($changes['pass'])) {
            try {
                $return = $adldap->user()->password($this->_userName($user),$changes['pass']);
            } catch (adLDAPException $e) {
                if ($this->conf['debug']) msg('AD Auth: '.$e->getMessage(), -1);
                $return = false;
            }
            if(!$return) msg('AD Auth: failed to change the password. Maybe the password policy was not met?', -1);
        }

        // changing user data
        $adchanges = array();
        if(isset($changes['name'])) {
            // get first and last name
            $parts                     = explode(' ', $changes['name']);
            $adchanges['surname']      = array_pop($parts);
            $adchanges['firstname']    = join(' ', $parts);
            $adchanges['display_name'] = $changes['name'];
        }
        if(isset($changes['mail'])) {
            $adchanges['email'] = $changes['mail'];
        }
        if(count($adchanges)) {
            try {
                $return = $return & $adldap->user()->modify($this->_userName($user),$adchanges);
            } catch (adLDAPException $e) {
                if ($this->conf['debug']) msg('AD Auth: '.$e->getMessage(), -1);
                $return = false;
            }
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
    protected function _adldap($domain) {
        if(is_null($domain) && is_array($this->opts)) {
            $domain = $this->opts['domain'];
        }

        $this->opts = $this->_loadServerConfig((string) $domain);
        if(isset($this->adldap[$domain])) return $this->adldap[$domain];

        // connect
        try {
            $this->adldap[$domain] = new adLDAP($this->opts);
            return $this->adldap[$domain];
        } catch(adLDAPException $e) {
            if($this->conf['debug']) {
                msg('AD Auth: '.$e->getMessage(), -1);
            }
            $this->success         = false;
            $this->adldap[$domain] = null;
        }
        return false;
    }

    /**
     * Get the domain part from a user
     *
     * @param $user
     * @return string
     */
    public function _userDomain($user) {
        list(, $domain) = explode('@', $user, 2);
        return $domain;
    }

    /**
     * Get the user part from a user
     *
     * @param $user
     * @return string
     */
    public function _userName($user) {
        list($name) = explode('@', $user, 2);
        return $name;
    }

    /**
     * Fetch the configuration for the given AD domain
     *
     * @param string $domain current AD domain
     * @return array
     */
    protected function _loadServerConfig($domain) {
        // prepare adLDAP standard configuration
        $opts = $this->conf;

        $opts['domain'] = $domain;

        // add possible domain specific configuration
        if($domain && is_array($this->conf[$domain])) foreach($this->conf[$domain] as $key => $val) {
            $opts[$key] = $val;
        }

        // handle multiple AD servers
        $opts['domain_controllers'] = explode(',', $opts['domain_controllers']);
        $opts['domain_controllers'] = array_map('trim', $opts['domain_controllers']);
        $opts['domain_controllers'] = array_filter($opts['domain_controllers']);

        // compatibility with old option name
        if(empty($opts['admin_username']) && !empty($opts['ad_username'])) $opts['admin_username'] = $opts['ad_username'];
        if(empty($opts['admin_password']) && !empty($opts['ad_password'])) $opts['admin_password'] = $opts['ad_password'];

        // we can change the password if SSL is set
        if($opts['use_ssl'] || $opts['use_tls']) {
            $this->cando['modPass'] = true;
        } else {
            $this->cando['modPass'] = false;
        }

        // adLDAP expects empty user/pass as NULL, we're less strict FS#2781
        if(empty($opts['admin_username'])) $opts['admin_username'] = null;
        if(empty($opts['admin_password'])) $opts['admin_password'] = null;

        // user listing needs admin priviledges
        if(!empty($opts['admin_username']) && !empty($opts['admin_password'])) {
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
    public function _getConfiguredDomains() {
        $domains = array();
        if(empty($this->conf['account_suffix'])) return $domains; // not configured yet

        // add default domain, using the name from account suffix
        $domains[''] = ltrim($this->conf['account_suffix'], '@');

        // find additional domains
        foreach($this->conf as $key => $val) {
            if(is_array($val) && isset($val['account_suffix'])) {
                $domains[$key] = ltrim($val['account_suffix'], '@');
            }
        }
        ksort($domains);

        return $domains;
    }

    /**
     * Check provided user and userinfo for matching patterns
     *
     * The patterns are set up with $this->_constructPattern()
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     * @param string $user
     * @param array  $info
     * @return bool
     */
    protected function _filter($user, $info) {
        foreach($this->_pattern as $item => $pattern) {
            if($item == 'user') {
                if(!preg_match($pattern, $user)) return false;
            } else if($item == 'grps') {
                if(!count(preg_grep($pattern, $info['grps']))) return false;
            } else {
                if(!preg_match($pattern, $info[$item])) return false;
            }
        }
        return true;
    }

    /**
     * Create a pattern for $this->_filter()
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     * @param array $filter
     */
    protected function _constructPattern($filter) {
        $this->_pattern = array();
        foreach($filter as $item => $pattern) {
            $this->_pattern[$item] = '/'.str_replace('/', '\/', $pattern).'/i'; // allow regex characters
        }
    }
}
