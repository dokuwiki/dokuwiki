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

        if(empty($this->cnf['groupkey']))   $this->cnf['groupkey']   = 'cn';
        if(empty($this->cnf['userscope']))  $this->cnf['userscope']  = 'sub';
        if(empty($this->cnf['groupscope'])) $this->cnf['groupscope'] = 'sub';

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
            list($loginuser,$loginsticky,$loginpass) = auth_getCookie();
            if($loginuser && $loginpass){
                $loginpass = PMA_blowfish_decrypt($loginpass, auth_cookiesalt(!$loginsticky));
                $this->checkPass($loginuser, $loginpass);
            }
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

        $sr     = $this->_ldapsearch($this->con, $base, $filter, $this->cnf['userscope']);
        $result = @ldap_get_entries($this->con, $sr);
        if($this->cnf['debug']){
            msg('LDAP user search: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
            msg('LDAP search at: '.htmlspecialchars($base.' '.$filter),0,__LINE__,__FILE__);
        }

        // Don't accept more or less than one response
        if(!is_array($result) || $result['count'] != 1){
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
        if ($this->cnf['grouptree'] || $this->cnf['groupfilter']) {
            $base   = $this->_makeFilter($this->cnf['grouptree'], $user_result);
            $filter = $this->_makeFilter($this->cnf['groupfilter'], $user_result);
            $sr = $this->_ldapsearch($this->con, $base, $filter, $this->cnf['groupscope'], array($this->cnf['groupkey']));
            if($this->cnf['debug']){
                msg('LDAP group search: '.htmlspecialchars(ldap_error($this->con)),0,__LINE__,__FILE__);
                msg('LDAP search at: '.htmlspecialchars($base.' '.$filter),0,__LINE__,__FILE__);
            }
            if(!$sr){
                msg("LDAP: Reading group memberships failed",-1);
                return false;
            }
            $result = ldap_get_entries($this->con, $sr);
            ldap_free_result($sr);

            if(is_array($result)) foreach($result as $grp){
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
        if(!$this->_openLDAP()) return false;

        if (!isset($this->users)) {
            // Perform the search and grab all their details
            if(!empty($this->cnf['userfilter'])) {
                $all_filter = str_replace('%{user}', '*', $this->cnf['userfilter']);
            } else {
                $all_filter = "(ObjectClass=*)";
            }
            $sr=ldap_search($this->con,$this->cnf['usertree'],$all_filter);
            $entries = ldap_get_entries($this->con, $sr);
            $users_array = array();
            for ($i=0; $i<$entries["count"]; $i++){
                array_push($users_array, $entries[$i]["uid"][0]);
            }
            asort($users_array);
            $result = $users_array;
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

        $this->canDo['getUsers'] = true;
        return true;
    }

    /**
     * Wraps around ldap_search, ldap_list or ldap_read depending on $scope
     *
     * @param  $scope string - can be 'base', 'one' or 'sub'
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _ldapsearch($link_identifier, $base_dn, $filter, $scope='sub', $attributes=null,
                         $attrsonly=0, $sizelimit=0, $timelimit=0, $deref=LDAP_DEREF_NEVER){
        if(is_null($attributes)) $attributes = array();

        if($scope == 'base'){
            return @ldap_read($link_identifier, $base_dn, $filter, $attributes,
                             $attrsonly, $sizelimit, $timelimit, $deref);
        }elseif($scope == 'one'){
            return @ldap_list($link_identifier, $base_dn, $filter, $attributes,
                             $attrsonly, $sizelimit, $timelimit, $deref);
        }else{
            return @ldap_search($link_identifier, $base_dn, $filter, $attributes,
                                $attrsonly, $sizelimit, $timelimit, $deref);
        }
    }
}

//Setup VIM: ex: et ts=4 :
