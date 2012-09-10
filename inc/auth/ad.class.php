<?php
/**
 * Active Directory authentication backend for DokuWiki
 *
 * This makes authentication with a Active Directory server much easier
 * than when using the normal LDAP backend by utilizing the adLDAP library
 *
 * Usage:
 *   Set DokuWiki's local.protected.php auth setting to read
 *
 *   $conf['useacl']         = 1;
 *   $conf['disableactions'] = 'register';
 *   $conf['autopasswd']     = 0;
 *   $conf['authtype']       = 'ad';
 *   $conf['passcrypt']      = 'ssha';
 *
 *   $conf['auth']['ad']['account_suffix']     = '@my.domain.org';
 *   $conf['auth']['ad']['base_dn']            = 'DC=my,DC=domain,DC=org';
 *   $conf['auth']['ad']['domain_controllers'] = 'srv1.domain.org,srv2.domain.org';
 *
 *   //optional:
 *   $conf['auth']['ad']['sso']                = 1;
 *   $conf['auth']['ad']['ad_username']        = 'root';
 *   $conf['auth']['ad']['ad_password']        = 'pass';
 *   $conf['auth']['ad']['real_primarygroup']  = 1;
 *   $conf['auth']['ad']['use_ssl']            = 1;
 *   $conf['auth']['ad']['use_tls']            = 1;
 *   $conf['auth']['ad']['debug']              = 1;
 *
 *   // get additional information to the userinfo array
 *   // add a list of comma separated ldap contact fields.
 *   $conf['auth']['ad']['additional'] = 'field1,field2';
 *
 *  @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *  @author  James Van Lommel <jamesvl@gmail.com>
 *  @link    http://www.nosq.com/blog/2005/08/ldap-activedirectory-and-dokuwiki/
 *  @author  Andreas Gohr <andi@splitbrain.org>
 */

require_once(DOKU_INC.'inc/adLDAP.php');

class auth_ad extends auth_basic {
    var $cnf = null;
    var $opts = null;
    var $adldap = null;
    var $users = null;

    /**
     * Constructor
     */
    function auth_ad() {
        global $conf;
        $this->cnf = $conf['auth']['ad'];


        // additional information fields
        if (isset($this->cnf['additional'])) {
            $this->cnf['additional'] = str_replace(' ', '', $this->cnf['additional']);
            $this->cnf['additional'] = explode(',', $this->cnf['additional']);
        } else $this->cnf['additional'] = array();

        // ldap extension is needed
        if (!function_exists('ldap_connect')) {
            if ($this->cnf['debug'])
                msg("AD Auth: PHP LDAP extension not found.",-1);
            $this->success = false;
            return;
        }

        // Prepare SSO
        if($_SERVER['REMOTE_USER'] && $this->cnf['sso']){
             // remove possible NTLM domain
             list($dom,$usr) = explode('\\',$_SERVER['REMOTE_USER'],2);
             if(!$usr) $usr = $dom;

             // remove possible Kerberos domain
             list($usr,$dom) = explode('@',$usr);

             $dom = strtolower($dom);
             $_SERVER['REMOTE_USER'] = $usr;

             // we need to simulate a login
             if(empty($_COOKIE[DOKU_COOKIE])){
                 $_REQUEST['u'] = $_SERVER['REMOTE_USER'];
                 $_REQUEST['p'] = 'sso_only';
             }
        }

        // prepare adLDAP standard configuration
        $this->opts = $this->cnf;

        // add possible domain specific configuration
        if($dom && is_array($this->cnf[$dom])) foreach($this->cnf[$dom] as $key => $val){
            $this->opts[$key] = $val;
        }

        // handle multiple AD servers
        $this->opts['domain_controllers'] = explode(',',$this->opts['domain_controllers']);
        $this->opts['domain_controllers'] = array_map('trim',$this->opts['domain_controllers']);
        $this->opts['domain_controllers'] = array_filter($this->opts['domain_controllers']);

        // we can change the password if SSL is set
        if($this->opts['use_ssl'] || $this->opts['use_tls']){
            $this->cando['modPass'] = true;
        }
        $this->cando['modName'] = true;
        $this->cando['modMail'] = true;
    }

    /**
     * Check user+password [required auth function]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct by trying to bind
     * to the LDAP server
     *
     * @author  James Van Lommel <james@nosq.com>
     * @return  bool
     */
    function checkPass($user, $pass){
        if($_SERVER['REMOTE_USER'] &&
           $_SERVER['REMOTE_USER'] == $user &&
           $this->cnf['sso']) return true;

        if(!$this->_init()) return false;
        return $this->adldap->authenticate($user, $pass);
    }

    /**
     * Return user info [required auth function]
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email address of the user
     * grps array   list of groups the user is in
     *
     * This LDAP specific function returns the following
     * addional fields:
     *
     * dn   string  distinguished name (DN)
     * uid  string  Posix User ID
     *
     * @author  James Van Lommel <james@nosq.com>
     */
   function getUserData($user){
        global $conf;
        if(!$this->_init()) return false;

        $fields = array('mail','displayname','samaccountname');

        // add additional fields to read
        $fields = array_merge($fields, $this->cnf['additional']);
        $fields = array_unique($fields);

        //get info for given user
        $result = $this->adldap->user_info($user, $fields);
        //general user info
        $info['name'] = $result[0]['displayname'][0];
        $info['mail'] = $result[0]['mail'][0];
        $info['uid']  = $result[0]['samaccountname'][0];
        $info['dn']   = $result[0]['dn'];

        // additional information
        foreach ($this->cnf['additional'] as $field) {
            if (isset($result[0][strtolower($field)])) {
                $info[$field] = $result[0][strtolower($field)][0];
            }
        }

        // handle ActiveDirectory memberOf
        $info['grps'] = $this->adldap->user_groups($user,(bool) $this->opts['recursive_groups']);

        if (is_array($info['grps'])) {
            foreach ($info['grps'] as $ndx => $group) {
                $info['grps'][$ndx] = $this->cleanGroup($group);
            }
        }

        // always add the default group to the list of groups
        if(!is_array($info['grps']) || !in_array($conf['defaultgroup'],$info['grps'])){
            $info['grps'][] = $conf['defaultgroup'];
        }

        return $info;
    }

    /**
     * Make AD group names usable by DokuWiki.
     *
     * Removes backslashes ('\'), pound signs ('#'), and converts spaces to underscores.
     *
     * @author  James Van Lommel (jamesvl@gmail.com)
     */
    function cleanGroup($name) {
        $sName = str_replace('\\', '', $name);
        $sName = str_replace('#', '', $sName);
        $sName = preg_replace('[\s]', '_', $sName);
        return $sName;
    }

    /**
     * Sanitize user names
     */
    function cleanUser($name) {
        return $this->cleanGroup($name);
    }

    /**
     * Most values in LDAP are case-insensitive
     */
    function isCaseSensitive(){
        return false;
    }

    /**
     * Bulk retrieval of user data
     *
     * @author  Dominik Eckelmann <dokuwiki@cosmocode.de>
     * @param   start     index of first user to be returned
     * @param   limit     max number of users to be returned
     * @param   filter    array of field/pattern pairs, null for no filter
     * @return  array of userinfo (refer getUserData for internal userinfo details)
     */
    function retrieveUsers($start=0,$limit=-1,$filter=array()) {
        if(!$this->_init()) return false;

        if ($this->users === null) {
            //get info for given user
            $result = $this->adldap->all_users();
            if (!$result) return array();
            $this->users = array_fill_keys($result, false);
        }

        $i = 0;
        $count = 0;
        $this->_constructPattern($filter);
        $result = array();

        foreach ($this->users as $user => &$info) {
            if ($i++ < $start) {
                continue;
            }
            if ($info === false) {
                $info = $this->getUserData($user);
            }
            if ($this->_filter($user, $info)) {
                $result[$user] = $info;
                if (($limit >= 0) && (++$count >= $limit)) break;
            }
        }
        return $result;
    }

    /**
     * Modify user data
     *
     * @param   $user      nick of the user to be changed
     * @param   $changes   array of field/value pairs to be changed
     * @return  bool
    */
    function modifyUser($user, $changes) {
        $return = true;

        // password changing
        if(isset($changes['pass'])){
            try {
                $return = $this->adldap->user_password($user,$changes['pass']);
            } catch (adLDAPException $e) {
                if ($this->cnf['debug']) msg('AD Auth: '.$e->getMessage(), -1);
                $return = false;
            }
            if(!$return) msg('AD Auth: failed to change the password. Maybe the password policy was not met?',-1);
        }

        // changing user data
        $adchanges = array();
        if(isset($changes['name'])){
            // get first and last name
            $parts = explode(' ',$changes['name']);
            $adchanges['surname']   = array_pop($parts);
            $adchanges['firstname'] = join(' ',$parts);
            $adchanges['display_name'] = $changes['name'];
        }
        if(isset($changes['mail'])){
            $adchanges['email'] = $changes['mail'];
        }
        if(count($adchanges)){
            try {
                $return = $return & $this->adldap->user_modify($user,$adchanges);
            } catch (adLDAPException $e) {
                if ($this->cnf['debug']) msg('AD Auth: '.$e->getMessage(), -1);
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Initialize the AdLDAP library and connect to the server
     */
    function _init(){
        if(!is_null($this->adldap)) return true;

        // connect
        try {
            $this->adldap = new adLDAP($this->opts);
            if (isset($this->opts['ad_username']) && isset($this->opts['ad_password'])) {
                $this->canDo['getUsers'] = true;
            }
            return true;
        } catch (adLDAPException $e) {
            if ($this->cnf['debug']) {
                msg('AD Auth: '.$e->getMessage(), -1);
            }
            $this->success = false;
            $this->adldap  = null;
        }
        return false;
    }

    /**
     * return 1 if $user + $info match $filter criteria, 0 otherwise
     *
     * @author   Chris Smith <chris@jalakai.co.uk>
     */
    function _filter($user, $info) {
        foreach ($this->_pattern as $item => $pattern) {
            if ($item == 'user') {
                if (!preg_match($pattern, $user)) return 0;
            } else if ($item == 'grps') {
                if (!count(preg_grep($pattern, $info['grps']))) return 0;
            } else {
                if (!preg_match($pattern, $info[$item])) return 0;
            }
        }
        return 1;
    }

    function _constructPattern($filter) {
        $this->_pattern = array();
        foreach ($filter as $item => $pattern) {
//          $this->_pattern[$item] = '/'.preg_quote($pattern,"/").'/i';          // don't allow regex characters
            $this->_pattern[$item] = '/'.str_replace('/','\/',$pattern).'/i';    // allow regex characters
        }
    }
}

//Setup VIM: ex: et ts=4 :
