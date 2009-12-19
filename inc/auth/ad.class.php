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
 *   $conf['auth']['ad']['debug']              = 1;
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

    /**
     * Constructor
     */
    function auth_ad() {
        global $conf;
        $this->cnf = $conf['auth']['ad'];

        // ldap extension is needed
        if (!function_exists('ldap_connect')) {
            if ($this->cnf['debug'])
                msg("LDAP err: PHP LDAP extension not found.",-1);
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

        // we currently just handle authentication, so no capabilities are set
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
     * mail string  email addres of the user
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

        //get info for given user
        $result = $this->adldap->user_info($user);

        //general user info
        $info['name'] = $result[0]['displayname'][0];
        $info['mail'] = $result[0]['mail'][0];
        $info['uid']  = $result[0]['samaccountname'][0];
        $info['dn']   = $result[0]['dn'];

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
     * Initialize the AdLDAP library and connect to the server
     */
    function _init(){
        if(!is_null($this->adldap)) return true;

        // connect
        try {
            $this->adldap = new adLDAP($this->opts);
            return true;
        } catch (adLDAPException $e) {
            $this->success = false;
            $this->adldap  = null;
        }
        return false;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
