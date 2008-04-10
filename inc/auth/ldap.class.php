<?php
/**
 * LDAP authentication backend
 *
 * @license   GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author    Andreas Gohr <andi@splitbrain.org>
 * @author    Chris Smith <chris@jalakaic.co.uk>
 */

class auth_ldap extends auth_basic {
    var $cnf = null;
    var $con = null;
    var $bound = 0; // 0: anonymous, 1: user, 2: superuser

    /**
     * Constructor
     */
    function auth_ldap(){
        global $conf;
        $this->cnf = $conf['auth']['ldap'];

        // ldap extension is needed
        if(!function_exists('ldap_connect')) {
            if ($this->cnf['debug'])
                msg("LDAP err: PHP LDAP extension not found.",-1,__LINE__,__FILE__);
            $this->success = false;
            return;
        }

        if(empty($this->cnf['groupkey'])) $this->cnf['groupkey'] = 'cn';

        // try to connect
        if(!$this->_openLDAP()) $this->success = false;

        // auth_ldap currently just handles authentication, so no
        // capabilities are set
    }

    /**
     * Check user+password
     *
     * Checks if the given user exists and the given
     * plaintext password is correct by trying to bind
     * to the LDAP server
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @return  bool
     */
    function checkPass($user,$pass){
        // reject empty password
        if(empty($pass)) return false;
        if(!$this->_openLDAP()) return false;

        // indirect user bind
        if($this->cnf['binddn'] && $this->cnf['bindpw']){
            // use superuser credentials
            if(!@ldap_bind($this->con,$this->cnf['binddn'],$this->cnf['bindpw'])){
                if($this->cnf['debug'])
                    msg('LDAP bind as superuser: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                return false;
            }
            $this->bound = 2;
        }else if($this->cnf['binddn'] &&
                 $this->cnf['usertree'] &&
                 $this->cnf['userfilter']) {
            // special bind string
            $dn = $this->_makeFilter($this->cnf['binddn'],
                                     array('user'=>$user,'server'=>$this->cnf['server']));

        }else if(strpos($this->cnf['usertree'], '%{user}')) {
            // direct user bind
            $dn = $this->_makeFilter($this->cnf['usertree'],
                                     array('user'=>$user,'server'=>$this->cnf['server']));

        }else{
            // Anonymous bind
            if(!@ldap_bind($this->con)){
                msg("LDAP: can not bind anonymously",-1);
                if($this->cnf['debug'])
                    msg('LDAP anonymous bind: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                return false;
            }
        }

        // Try to bind to with the dn if we have one.
        if(!empty($dn)) {
            // User/Password bind
            if(!@ldap_bind($this->con,$dn,$pass)){
                if($this->cnf['debug']){
                    msg("LDAP: bind with $dn failed", -1,__LINE__,__FILE__);
                    msg('LDAP user dn bind: '.htmlspecialchars(ldap_error($this->con)),0);
                }
                return false;
            }
            $this->bound = 1;
            return true;
        }else{
            // See if we can find the user
            $info = $this->getUserData($user,true);
            if(empty($info['dn'])) {
                return false;
            } else {
                $dn = $info['dn'];
            }

            // Try to bind with the dn provided
            if(!@ldap_bind($this->con,$dn,$pass)){
                if($this->cnf['debug']){
                    msg("LDAP: bind with $dn failed", -1,__LINE__,__FILE__);
                    msg('LDAP user bind: '.htmlspecialchars(ldap_error($this->con)),0);
                }
                return false;
            }
            $this->bound = 1;
            return true;
        }

        return false;
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
     * @return  array containing user data or false
     */
    function getUserData($user,$inbind=false) {
        global $conf;
        if(!$this->_openLDAP()) return false;

        // force superuser bind if wanted and not bound as superuser yet
        if($this->cnf['binddn'] && $this->cnf['bindpw'] && $this->bound < 2){
            // use superuser credentials
            if(!@ldap_bind($this->con,$this->cnf['binddn'],$this->cnf['bindpw'])){
                if($this->cnf['debug'])
                    msg('LDAP bind as superuser: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                return false;
            }
            $this->bound = 2;
        }elseif($this->bound == 0 && !$inbind) {
            // in some cases getUserData is called outside the authentication workflow
            // eg. for sending email notification on subscribed pages. This data might not
            // be accessible anonymously, so we try to rebind the current user here
            $pass = PMA_blowfish_decrypt($_SESSION[DOKU_COOKIE]['auth']['pass'],auth_cookiesalt());
            $this->checkPass($_SESSION[DOKU_COOKIE]['auth']['user'], $pass);
        }

        $info['user']   = $user;
        $info['server'] = $this->cnf['server'];

        //get info for given user
        $base = $this->_makeFilter($this->cnf['usertree'], $info);
        if(!empty($this->cnf['userfilter'])) {
            $filter = $this->_makeFilter($this->cnf['userfilter'], $info);
        } else {
            $filter = "(ObjectClass=*)";
        }

        $sr     = @ldap_search($this->con, $base, $filter);
        $result = @ldap_get_entries($this->con, $sr);
        if($this->cnf['debug'])
            msg('LDAP user search: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);

        // Don't accept more or less than one response
        if($result['count'] != 1){
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
        if(is_array($this->cnf['mapping'])){
            foreach($this->cnf['mapping'] as $localkey => $key) {
                if(is_array($key)) {
                    // use regexp to clean up user_result
                    list($key, $regexp) = each($key);
                    if($user_result[$key]) foreach($user_result[$key] as $grp){
                        if (preg_match($regexp,$grp,$match)) {
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
        $user_result = array_merge($info,$user_result);

        //get groups for given user if grouptree is given
        if ($this->cnf['grouptree'] && $this->cnf['groupfilter']) {
            $base   = $this->_makeFilter($this->cnf['grouptree'], $user_result);
            $filter = $this->_makeFilter($this->cnf['groupfilter'], $user_result);

            $sr = @ldap_search($this->con, $base, $filter, array($this->cnf['groupkey']));
            if(!$sr){
                msg("LDAP: Reading group memberships failed",-1);
                if($this->cnf['debug'])
                    msg('LDAP group search: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                return false;
            }
            $result = ldap_get_entries($this->con, $sr);
            ldap_free_result($sr);

            foreach($result as $grp){
                if(!empty($grp[$this->cnf['groupkey']][0])){
                    if($this->cnf['debug'])
                        msg('LDAP usergroup: '.htmlspecialchars($grp[$this->cnf['groupkey']][0]),0,__LINE__,__FILE__);
                    $info['grps'][] = $grp[$this->cnf['groupkey']][0];
                }
            }
        }

        // always add the default group to the list of groups
        if(!in_array($conf['defaultgroup'],$info['grps'])){
            $info['grps'][] = $conf['defaultgroup'];
        }
        return $info;
    }

    /**
     * Make LDAP filter strings.
     *
     * Used by auth_getUserData to make the filter
     * strings for grouptree and groupfilter
     *
     * filter      string  ldap search filter with placeholders
     * placeholders array   array with the placeholders
     *
     * @author  Troels Liebe Bentsen <tlb@rapanden.dk>
     * @return  string
     */
    function _makeFilter($filter, $placeholders) {
        preg_match_all("/%{([^}]+)/", $filter, $matches, PREG_PATTERN_ORDER);
        //replace each match
        foreach ($matches[1] as $match) {
            //take first element if array
            if(is_array($placeholders[$match])) {
                $value = $placeholders[$match][0];
            } else {
                $value = $placeholders[$match];
            }
            $value = $this->_filterEscape($value);
            $filter = str_replace('%{'.$match.'}', $value, $filter);
        }
        return $filter;
    }

    /**
     * Escape a string to be used in a LDAP filter
     *
     * Ported from Perl's Net::LDAP::Util escape_filter_value
     *
     * @author Andreas Gohr
     */
    function _filterEscape($string){
        return preg_replace('/([\x00-\x1F\*\(\)\\\\])/e',
                            '"\\\\\".join("",unpack("H2","$1"))',
                            $string);
    }

    /**
     * Opens a connection to the configured LDAP server and sets the wanted
     * option on the connection
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function _openLDAP(){
        if($this->con) return true; // connection already established

        $this->bound = 0;

        $port = ($this->cnf['port']) ? $this->cnf['port'] : 389;
        $this->con = @ldap_connect($this->cnf['server'],$port);
        if(!$this->con){
            msg("LDAP: couldn't connect to LDAP server",-1);
            return false;
        }

        //set protocol version and dependend options
        if($this->cnf['version']){
            if(!@ldap_set_option($this->con, LDAP_OPT_PROTOCOL_VERSION,
                                 $this->cnf['version'])){
                msg('Setting LDAP Protocol version '.$this->cnf['version'].' failed',-1);
                if($this->cnf['debug'])
                    msg('LDAP version set: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
            }else{
                //use TLS (needs version 3)
                if($this->cnf['starttls']) {
                    if (!@ldap_start_tls($this->con)){
                        msg('Starting TLS failed',-1);
                        if($this->cnf['debug'])
                            msg('LDAP TLS set: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                    }
                }
                // needs version 3
                if(isset($this->cnf['referrals'])) {
                    if(!@ldap_set_option($this->con, LDAP_OPT_REFERRALS,
                       $this->cnf['referrals'])){
                        msg('Setting LDAP referrals to off failed',-1);
                        if($this->cnf['debug'])
                            msg('LDAP referal set: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                    }
                }
            }
        }

        //set deref mode
        if($this->cnf['deref']){
            if(!@ldap_set_option($this->con, LDAP_OPT_DEREF, $this->cnf['deref'])){
                msg('Setting LDAP Deref mode '.$this->cnf['deref'].' failed',-1);
                if($this->cnf['debug'])
                    msg('LDAP deref set: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
            }
        }

        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
