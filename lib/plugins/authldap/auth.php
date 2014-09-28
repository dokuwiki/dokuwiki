<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * LDAP authentication backend
 *
 * @license   GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author    Andreas Gohr <andi@splitbrain.org>
 * @author    Chris Smith <chris@jalakaic.co.uk>
 * @author    Jan Schumann <js@schumann-it.com>
 */
class auth_plugin_authldap extends DokuWiki_Auth_Plugin {
    /* @var resource $con holds the LDAP connection*/
    protected $con = null;

    /* @var int $bound What type of connection does already exist? */
    protected $bound = 0; // 0: anonymous, 1: user, 2: superuser

    /* @var array $users User data cache */
    protected $users = null;

    /* @var array $_pattern User filter pattern */
    protected $_pattern = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        // ldap extension is needed
        if(!function_exists('ldap_connect')) {
            $this->_debug("LDAP err: PHP LDAP extension not found.", -1, __LINE__, __FILE__);
            $this->success = false;
            return;
        }

        // Add the capabilities to change the password
        $this->cando['modPass'] = true;
    }

    /**
     * Check user+password
     *
     * Checks if the given user exists and the given
     * plaintext password is correct by trying to bind
     * to the LDAP server
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param string $user
     * @param string $pass
     * @return  bool
     */
    public function checkPass($user, $pass) {
        // reject empty password
        if(empty($pass)) return false;
        if(!$this->_openLDAP()) return false;

        // indirect user bind
        if($this->getConf('binddn') && $this->getConf('bindpw')) {
            // use superuser credentials
            if(!@ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'))) {
                $this->_debug('LDAP bind as superuser: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
            $this->bound = 2;
        } else if($this->getConf('binddn') &&
            $this->getConf('usertree') &&
            $this->getConf('userfilter')
        ) {
            // special bind string
            $dn = $this->_makeFilter(
                $this->getConf('binddn'),
                array('user'=> $user, 'server'=> $this->getConf('server'))
            );

        } else if(strpos($this->getConf('usertree'), '%{user}')) {
            // direct user bind
            $dn = $this->_makeFilter(
                $this->getConf('usertree'),
                array('user'=> $user, 'server'=> $this->getConf('server'))
            );

        } else {
            // Anonymous bind
            if(!@ldap_bind($this->con)) {
                msg("LDAP: can not bind anonymously", -1);
                $this->_debug('LDAP anonymous bind: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
        }

        // Try to bind to with the dn if we have one.
        if(!empty($dn)) {
            // User/Password bind
            if(!@ldap_bind($this->con, $dn, $pass)) {
                $this->_debug("LDAP: bind with $dn failed", -1, __LINE__, __FILE__);
                $this->_debug('LDAP user dn bind: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
            $this->bound = 1;
            return true;
        } else {
            // See if we can find the user
            $info = $this->_getUserData($user, true);
            if(empty($info['dn'])) {
                return false;
            } else {
                $dn = $info['dn'];
            }

            // Try to bind with the dn provided
            if(!@ldap_bind($this->con, $dn, $pass)) {
                $this->_debug("LDAP: bind with $dn failed", -1, __LINE__, __FILE__);
                $this->_debug('LDAP user bind: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
            $this->bound = 1;
            return true;
        }
    }

    /**
     * Return user info
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email addres of the user
     * grps array   list of groups the user is in
     *
     * This LDAP specific function returns the following
     * addional fields:
     *
     * dn     string  distinguished name (DN)
     * uid    string  Posix User ID
     * inbind bool    for internal use - avoid loop in binding
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Trouble
     * @author  Dan Allen <dan.j.allen@gmail.com>
     * @author  <evaldas.auryla@pheur.org>
     * @author  Stephane Chazelas <stephane.chazelas@emerson.com>
     * @author  Steffen Schoch <schoch@dsb.net>
     *
     * @param   string $user
     * @param   bool   $requireGroups (optional) - ignored, groups are always supplied by this plugin
     * @return  array containing user data or false
     */
    public function getUserData($user, $requireGroups=true) {
        return $this->_getUserData($user);
    }

    /**
     * @param   string $user
     * @param   bool   $inbind authldap specific, true if in bind phase
     * @return  array containing user data or false
     */
    protected function _getUserData($user, $inbind = false) {
        global $conf;
        if(!$this->_openLDAP()) return false;

        // force superuser bind if wanted and not bound as superuser yet
        if($this->getConf('binddn') && $this->getConf('bindpw') && $this->bound < 2) {
            // use superuser credentials
            if(!@ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'))) {
                $this->_debug('LDAP bind as superuser: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
            $this->bound = 2;
        } elseif($this->bound == 0 && !$inbind) {
            // in some cases getUserData is called outside the authentication workflow
            // eg. for sending email notification on subscribed pages. This data might not
            // be accessible anonymously, so we try to rebind the current user here
            list($loginuser, $loginsticky, $loginpass) = auth_getCookie();
            if($loginuser && $loginpass) {
                $loginpass = auth_decrypt($loginpass, auth_cookiesalt(!$loginsticky, true));
                $this->checkPass($loginuser, $loginpass);
            }
        }

        $info['user']   = $user;
        $info['server'] = $this->getConf('server');

        //get info for given user
        $base = $this->_makeFilter($this->getConf('usertree'), $info);
        if($this->getConf('userfilter')) {
            $filter = $this->_makeFilter($this->getConf('userfilter'), $info);
        } else {
            $filter = "(ObjectClass=*)";
        }

        $sr     = $this->_ldapsearch($this->con, $base, $filter, $this->getConf('userscope'));
        $result = @ldap_get_entries($this->con, $sr);
        $this->_debug('LDAP user search: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
        $this->_debug('LDAP search at: '.htmlspecialchars($base.' '.$filter), 0, __LINE__, __FILE__);

        // Don't accept more or less than one response
        if(!is_array($result) || $result['count'] != 1) {
            return false; //user not found
        }

        $user_result = $result[0];
        ldap_free_result($sr);

        // general user info
        $info['dn']   = $user_result['dn'];
        $info['gid']  = $user_result['gidnumber'][0];
        $info['mail'] = $user_result['mail'][0];
        $info['name'] = $user_result['cn'][0];
        $info['grps'] = array();

        // overwrite if other attribs are specified.
        if(is_array($this->getConf('mapping'))) {
            foreach($this->getConf('mapping') as $localkey => $key) {
                if(is_array($key)) {
                    // use regexp to clean up user_result
                    list($key, $regexp) = each($key);
                    if($user_result[$key]) foreach($user_result[$key] as $grpkey => $grp) {
                        if($grpkey !== 'count' && preg_match($regexp, $grp, $match)) {
                            if($localkey == 'grps') {
                                $info[$localkey][] = $match[1];
                            } else {
                                $info[$localkey] = $match[1];
                            }
                        }
                    }
                } else {
                    $info[$localkey] = $user_result[$key][0];
                }
            }
        }
        $user_result = array_merge($info, $user_result);

        //get groups for given user if grouptree is given
        if($this->getConf('grouptree') || $this->getConf('groupfilter')) {
            $base   = $this->_makeFilter($this->getConf('grouptree'), $user_result);
            $filter = $this->_makeFilter($this->getConf('groupfilter'), $user_result);
            $sr     = $this->_ldapsearch($this->con, $base, $filter, $this->getConf('groupscope'), array($this->getConf('groupkey')));
            $this->_debug('LDAP group search: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
            $this->_debug('LDAP search at: '.htmlspecialchars($base.' '.$filter), 0, __LINE__, __FILE__);

            if(!$sr) {
                msg("LDAP: Reading group memberships failed", -1);
                return false;
            }
            $result = ldap_get_entries($this->con, $sr);
            ldap_free_result($sr);

            if(is_array($result)) foreach($result as $grp) {
                if(!empty($grp[$this->getConf('groupkey')])) {
                    $group = $grp[$this->getConf('groupkey')];
                    if(is_array($group)){
                        $group = $group[0];
                    } else {
                        $this->_debug('groupkey did not return a detailled result', 0, __LINE__, __FILE__);
                    }
                    if($group === '') continue;

                    $this->_debug('LDAP usergroup: '.htmlspecialchars($group), 0, __LINE__, __FILE__);
                    $info['grps'][] = $group;
                }
            }
        }

        // always add the default group to the list of groups
        if(!$info['grps'] or !in_array($conf['defaultgroup'], $info['grps'])) {
            $info['grps'][] = $conf['defaultgroup'];
        }
        return $info;
    }

    /**
     * Definition of the function modifyUser in order to modify the password
     */

    function modifyUser($user,$changes){

        // open the connection to the ldap
        if(!$this->_openLDAP()){
            msg('LDAP cannot connect: '. htmlspecialchars(ldap_error($this->con)));
            return false;
        }

        // find the information about the user, in particular the "dn"
        $info = $this->getUserData($user,true);
        if(empty($info['dn'])) {
            msg('LDAP cannot find your user dn');
            return false;
        }
        $dn = $info['dn'];

        // find the old password of the user
        list($loginuser,$loginsticky,$loginpass) = auth_getCookie();
        if ($loginuser !== null) { // the user is currently logged in
            $secret = auth_cookiesalt(!$loginsticky, true);
            $pass   = auth_decrypt($loginpass, $secret);

            // bind with the ldap
            if(!@ldap_bind($this->con, $dn, $pass)){
                msg('LDAP user bind failed: '. htmlspecialchars($dn) .': '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
        } elseif ($this->getConf('binddn') && $this->getConf('bindpw')) {
            // we are changing the password on behalf of the user (eg: forgotten password)
            // bind with the superuser ldap
            if (!@ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'))){
                $this->_debug('LDAP bind as superuser: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                return false;
            }
        }
        else {
            return false; // no otherway
        }

        // Generate the salted hashed password for LDAP
        $phash = new PassHash();
        $hash = $phash->hash_ssha($changes['pass']);

        // change the password
        if(!@ldap_mod_replace($this->con, $dn,array('userpassword' => $hash))){
            msg('LDAP mod replace failed: '. htmlspecialchars($dn) .': '.htmlspecialchars(ldap_error($this->con)));
            return false;
        }

        return true;
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
     * @param   array $filter  array of field/pattern pairs, null for no filter
     * @return  array of userinfo (refer getUserData for internal userinfo details)
     */
    function retrieveUsers($start = 0, $limit = 0, $filter = array()) {
        if(!$this->_openLDAP()) return false;

        if(is_null($this->users)) {
            // Perform the search and grab all their details
            if($this->getConf('userfilter')) {
                $all_filter = str_replace('%{user}', '*', $this->getConf('userfilter'));
            } else {
                $all_filter = "(ObjectClass=*)";
            }
            $sr          = ldap_search($this->con, $this->getConf('usertree'), $all_filter);
            $entries     = ldap_get_entries($this->con, $sr);
            $users_array = array();
            for($i = 0; $i < $entries["count"]; $i++) {
                array_push($users_array, $entries[$i]["uid"][0]);
            }
            asort($users_array);
            $result = $users_array;
            if(!$result) return array();
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
     * Make LDAP filter strings.
     *
     * Used by auth_getUserData to make the filter
     * strings for grouptree and groupfilter
     *
     * @author  Troels Liebe Bentsen <tlb@rapanden.dk>
     * @param   string $filter ldap search filter with placeholders
     * @param   array  $placeholders placeholders to fill in
     * @return  string
     */
    protected function _makeFilter($filter, $placeholders) {
        preg_match_all("/%{([^}]+)/", $filter, $matches, PREG_PATTERN_ORDER);
        //replace each match
        foreach($matches[1] as $match) {
            //take first element if array
            if(is_array($placeholders[$match])) {
                $value = $placeholders[$match][0];
            } else {
                $value = $placeholders[$match];
            }
            $value  = $this->_filterEscape($value);
            $filter = str_replace('%{'.$match.'}', $value, $filter);
        }
        return $filter;
    }

    /**
     * return true if $user + $info match $filter criteria, false otherwise
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     * @param  string $user the user's login name
     * @param  array  $info the user's userinfo array
     * @return bool
     */
    protected  function _filter($user, $info) {
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
     * Set the filter pattern
     *
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     * @param $filter
     * @return void
     */
    protected function _constructPattern($filter) {
        $this->_pattern = array();
        foreach($filter as $item => $pattern) {
            $this->_pattern[$item] = '/'.str_replace('/', '\/', $pattern).'/i'; // allow regex characters
        }
    }

    /**
     * Escape a string to be used in a LDAP filter
     *
     * Ported from Perl's Net::LDAP::Util escape_filter_value
     *
     * @author Andreas Gohr
     * @param  string $string
     * @return string
     */
    protected function _filterEscape($string) {
        return preg_replace(
            '/([\x00-\x1F\*\(\)\\\\])/e',
            '"\\\\\".join("",unpack("H2","$1"))',
            $string
        );
    }

    /**
     * Opens a connection to the configured LDAP server and sets the wanted
     * option on the connection
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    protected function _openLDAP() {
        if($this->con) return true; // connection already established

        $this->bound = 0;

        $port    = $this->getConf('port');
        $bound   = false;
        $servers = explode(',', $this->getConf('server'));
        foreach($servers as $server) {
            $server    = trim($server);
            $this->con = @ldap_connect($server, $port);
            if(!$this->con) {
                continue;
            }

            /*
             * When OpenLDAP 2.x.x is used, ldap_connect() will always return a resource as it does
             * not actually connect but just initializes the connecting parameters. The actual
             * connect happens with the next calls to ldap_* funcs, usually with ldap_bind().
             *
             * So we should try to bind to server in order to check its availability.
             */

            //set protocol version and dependend options
            if($this->getConf('version')) {
                if(!@ldap_set_option(
                    $this->con, LDAP_OPT_PROTOCOL_VERSION,
                    $this->getConf('version')
                )
                ) {
                    msg('Setting LDAP Protocol version '.$this->getConf('version').' failed', -1);
                    $this->_debug('LDAP version set: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                } else {
                    //use TLS (needs version 3)
                    if($this->getConf('starttls')) {
                        if(!@ldap_start_tls($this->con)) {
                            msg('Starting TLS failed', -1);
                            $this->_debug('LDAP TLS set: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                        }
                    }
                    // needs version 3
                    if($this->getConf('referrals')) {
                        if(!@ldap_set_option(
                            $this->con, LDAP_OPT_REFERRALS,
                            $this->getConf('referrals')
                        )
                        ) {
                            msg('Setting LDAP referrals to off failed', -1);
                            $this->_debug('LDAP referal set: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                        }
                    }
                }
            }

            //set deref mode
            if($this->getConf('deref')) {
                if(!@ldap_set_option($this->con, LDAP_OPT_DEREF, $this->getConf('deref'))) {
                    msg('Setting LDAP Deref mode '.$this->getConf('deref').' failed', -1);
                    $this->_debug('LDAP deref set: '.htmlspecialchars(ldap_error($this->con)), 0, __LINE__, __FILE__);
                }
            }
            /* As of PHP 5.3.0 we can set timeout to speedup skipping of invalid servers */
            if(defined('LDAP_OPT_NETWORK_TIMEOUT')) {
                ldap_set_option($this->con, LDAP_OPT_NETWORK_TIMEOUT, 1);
            }

            if($this->getConf('binddn') && $this->getConf('bindpw')) {
                $bound = @ldap_bind($this->con, $this->getConf('binddn'), $this->getConf('bindpw'));
                $this->bound = 2;
            } else {
                $bound = @ldap_bind($this->con);
            }
            if($bound) {
                break;
            }
        }

        if(!$bound) {
            msg("LDAP: couldn't connect to LDAP server", -1);
            return false;
        }

        $this->cando['getUsers'] = true;
        return true;
    }

    /**
     * Wraps around ldap_search, ldap_list or ldap_read depending on $scope
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param resource $link_identifier
     * @param string   $base_dn
     * @param string   $filter
     * @param string   $scope can be 'base', 'one' or 'sub'
     * @param null     $attributes
     * @param int      $attrsonly
     * @param int      $sizelimit
     * @param int      $timelimit
     * @param int      $deref
     * @return resource
     */
    protected function _ldapsearch($link_identifier, $base_dn, $filter, $scope = 'sub', $attributes = null,
                         $attrsonly = 0, $sizelimit = 0) {
        if(is_null($attributes)) $attributes = array();

        if($scope == 'base') {
            return @ldap_read(
                $link_identifier, $base_dn, $filter, $attributes,
                $attrsonly, $sizelimit
            );
        } elseif($scope == 'one') {
            return @ldap_list(
                $link_identifier, $base_dn, $filter, $attributes,
                $attrsonly, $sizelimit
            );
        } else {
            return @ldap_search(
                $link_identifier, $base_dn, $filter, $attributes,
                $attrsonly, $sizelimit
            );
        }
    }

    /**
     * Wrapper around msg() but outputs only when debug is enabled
     *
     * @param string $message
     * @param int    $err
     * @param int    $line
     * @param string $file
     * @return void
     */
    protected function _debug($message, $err, $line, $file) {
        if(!$this->getConf('debug')) return;
        msg($message, $err, $line, $file);
    }

}
