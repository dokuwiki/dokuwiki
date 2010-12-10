<?php
/**
 * Authentication library
 *
 * Including this file will automatically try to login
 * a user by calling auth_login()
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

// some ACL level defines
define('AUTH_NONE',0);
define('AUTH_READ',1);
define('AUTH_EDIT',2);
define('AUTH_CREATE',4);
define('AUTH_UPLOAD',8);
define('AUTH_DELETE',16);
define('AUTH_ADMIN',255);

/**
 * Initialize the auth system.
 *
 * This function is automatically called at the end of init.php
 *
 * This used to be the main() of the auth.php
 *
 * @todo backend loading maybe should be handled by the class autoloader
 * @todo maybe split into multiple functions at the XXX marked positions
 */
function auth_setup(){
    global $conf;
    global $auth;
    global $AUTH_ACL;
    global $lang;
    global $config_cascade;
    $AUTH_ACL = array();

    if(!$conf['useacl']) return false;

    // load the the backend auth functions and instantiate the auth object XXX
    if (@file_exists(DOKU_INC.'inc/auth/'.$conf['authtype'].'.class.php')) {
        require_once(DOKU_INC.'inc/auth/basic.class.php');
        require_once(DOKU_INC.'inc/auth/'.$conf['authtype'].'.class.php');

        $auth_class = "auth_".$conf['authtype'];
        if (class_exists($auth_class)) {
            $auth = new $auth_class();
            if ($auth->success == false) {
                // degrade to unauthenticated user
                unset($auth);
                auth_logoff();
                msg($lang['authtempfail'], -1);
            }
        } else {
            nice_die($lang['authmodfailed']);
        }
    } else {
        nice_die($lang['authmodfailed']);
    }

    if(!$auth) return;

    // do the login either by cookie or provided credentials XXX
    if (!isset($_REQUEST['u'])) $_REQUEST['u'] = '';
    if (!isset($_REQUEST['p'])) $_REQUEST['p'] = '';
    if (!isset($_REQUEST['r'])) $_REQUEST['r'] = '';
    $_REQUEST['http_credentials'] = false;
    if (!$conf['rememberme']) $_REQUEST['r'] = false;

    // streamline HTTP auth credentials (IIS/rewrite -> mod_php)
    if(isset($_SERVER['HTTP_AUTHORIZATION'])){
        list($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']) =
            explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    }

    // if no credentials were given try to use HTTP auth (for SSO)
    if(empty($_REQUEST['u']) && empty($_COOKIE[DOKU_COOKIE]) && !empty($_SERVER['PHP_AUTH_USER'])){
        $_REQUEST['u'] = $_SERVER['PHP_AUTH_USER'];
        $_REQUEST['p'] = $_SERVER['PHP_AUTH_PW'];
        $_REQUEST['http_credentials'] = true;
    }

    // apply cleaning
    $_REQUEST['u'] = $auth->cleanUser($_REQUEST['u']);

    if(isset($_REQUEST['authtok'])){
        // when an authentication token is given, trust the session
        auth_validateToken($_REQUEST['authtok']);
    }elseif(!is_null($auth) && $auth->canDo('external')){
        // external trust mechanism in place
        $auth->trustExternal($_REQUEST['u'],$_REQUEST['p'],$_REQUEST['r']);
    }else{
        $evdata = array(
                'user'     => $_REQUEST['u'],
                'password' => $_REQUEST['p'],
                'sticky'   => $_REQUEST['r'],
                'silent'   => $_REQUEST['http_credentials'],
                );
        trigger_event('AUTH_LOGIN_CHECK', $evdata, 'auth_login_wrapper');
    }

    //load ACL into a global array XXX
    $AUTH_ACL = auth_loadACL();
}

/**
 * Loads the ACL setup and handle user wildcards
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @returns array
 */
function auth_loadACL(){
    global $config_cascade;

    if(!is_readable($config_cascade['acl']['default'])) return array();

    $acl = file($config_cascade['acl']['default']);

    //support user wildcard
    if(isset($_SERVER['REMOTE_USER'])){
        $len = count($acl);
        for($i=0; $i<$len; $i++){
            if($acl[$i]{0} == '#') continue;
            list($id,$rest) = preg_split('/\s+/',$acl[$i],2);
            $id   = str_replace('%USER%',cleanID($_SERVER['REMOTE_USER']),$id);
            $rest = str_replace('%USER%',auth_nameencode($_SERVER['REMOTE_USER']),$rest);
            $acl[$i] = "$id\t$rest";
        }
    }
    return $acl;
}

function auth_login_wrapper($evdata) {
    return auth_login($evdata['user'],
                      $evdata['password'],
                      $evdata['sticky'],
                      $evdata['silent']);
}

/**
 * This tries to login the user based on the sent auth credentials
 *
 * The authentication works like this: if a username was given
 * a new login is assumed and user/password are checked. If they
 * are correct the password is encrypted with blowfish and stored
 * together with the username in a cookie - the same info is stored
 * in the session, too. Additonally a browserID is stored in the
 * session.
 *
 * If no username was given the cookie is checked: if the username,
 * crypted password and browserID match between session and cookie
 * no further testing is done and the user is accepted
 *
 * If a cookie was found but no session info was availabe the
 * blowfish encrypted password from the cookie is decrypted and
 * together with username rechecked by calling this function again.
 *
 * On a successful login $_SERVER[REMOTE_USER] and $USERINFO
 * are set.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param   string  $user    Username
 * @param   string  $pass    Cleartext Password
 * @param   bool    $sticky  Cookie should not expire
 * @param   bool    $silent  Don't show error on bad auth
 * @return  bool             true on successful auth
 */
function auth_login($user,$pass,$sticky=false,$silent=false){
    global $USERINFO;
    global $conf;
    global $lang;
    global $auth;
    $sticky ? $sticky = true : $sticky = false; //sanity check

    if (!$auth) return false;

    if(!empty($user)){
        //usual login
        if ($auth->checkPass($user,$pass)){
            // make logininfo globally available
            $_SERVER['REMOTE_USER'] = $user;
            auth_setCookie($user,PMA_blowfish_encrypt($pass,auth_cookiesalt()),$sticky);
            return true;
        }else{
            //invalid credentials - log off
            if(!$silent) msg($lang['badlogin'],-1);
            auth_logoff();
            return false;
        }
    }else{
        // read cookie information
        list($user,$sticky,$pass) = auth_getCookie();
        // get session info
        $session = $_SESSION[DOKU_COOKIE]['auth'];
        if($user && $pass){
            // we got a cookie - see if we can trust it
            if(isset($session) &&
                    $auth->useSessionCache($user) &&
                    ($session['time'] >= time()-$conf['auth_security_timeout']) &&
                    ($session['user'] == $user) &&
                    ($session['pass'] == $pass) &&  //still crypted
                    ($session['buid'] == auth_browseruid()) ){
                // he has session, cookie and browser right - let him in
                $_SERVER['REMOTE_USER'] = $user;
                $USERINFO = $session['info']; //FIXME move all references to session
                return true;
            }
            // no we don't trust it yet - recheck pass but silent
            $pass = PMA_blowfish_decrypt($pass,auth_cookiesalt());
            return auth_login($user,$pass,$sticky,true);
        }
    }
    //just to be sure
    auth_logoff(true);
    return false;
}

/**
 * Checks if a given authentication token was stored in the session
 *
 * Will setup authentication data using data from the session if the
 * token is correct. Will exit with a 401 Status if not.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $token The authentication token
 * @return boolean true (or will exit on failure)
 */
function auth_validateToken($token){
    if(!$token || $token != $_SESSION[DOKU_COOKIE]['auth']['token']){
        // bad token
        header("HTTP/1.0 401 Unauthorized");
        print 'Invalid auth token - maybe the session timed out';
        unset($_SESSION[DOKU_COOKIE]['auth']['token']); // no second chance
        exit;
    }
    // still here? trust the session data
    global $USERINFO;
    $_SERVER['REMOTE_USER'] = $_SESSION[DOKU_COOKIE]['auth']['user'];
    $USERINFO = $_SESSION[DOKU_COOKIE]['auth']['info'];
    return true;
}

/**
 * Create an auth token and store it in the session
 *
 * NOTE: this is completely unrelated to the getSecurityToken() function
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @return string The auth token
 */
function auth_createToken(){
    $token = md5(mt_rand());
    @session_start(); // reopen the session if needed
    $_SESSION[DOKU_COOKIE]['auth']['token'] = $token;
    session_write_close();
    return $token;
}

/**
 * Builds a pseudo UID from browser and IP data
 *
 * This is neither unique nor unfakable - still it adds some
 * security. Using the first part of the IP makes sure
 * proxy farms like AOLs are stil okay.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @return  string  a MD5 sum of various browser headers
 */
function auth_browseruid(){
    $ip   = clientIP(true);
    $uid  = '';
    $uid .= $_SERVER['HTTP_USER_AGENT'];
    $uid .= $_SERVER['HTTP_ACCEPT_ENCODING'];
    $uid .= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $uid .= $_SERVER['HTTP_ACCEPT_CHARSET'];
    $uid .= substr($ip,0,strpos($ip,'.'));
    return md5($uid);
}

/**
 * Creates a random key to encrypt the password in cookies
 *
 * This function tries to read the password for encrypting
 * cookies from $conf['metadir'].'/_htcookiesalt'
 * if no such file is found a random key is created and
 * and stored in this file.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @return  string
 */
function auth_cookiesalt(){
    global $conf;
    $file = $conf['metadir'].'/_htcookiesalt';
    $salt = io_readFile($file);
    if(empty($salt)){
        $salt = uniqid(rand(),true);
        io_saveFile($file,$salt);
    }
    return $salt;
}

/**
 * Log out the current user
 *
 * This clears all authentication data and thus log the user
 * off. It also clears session data.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @param bool $keepbc - when true, the breadcrumb data is not cleared
 */
function auth_logoff($keepbc=false){
    global $conf;
    global $USERINFO;
    global $INFO, $ID;
    global $auth;

    // make sure the session is writable (it usually is)
    @session_start();

    if(isset($_SESSION[DOKU_COOKIE]['auth']['user']))
        unset($_SESSION[DOKU_COOKIE]['auth']['user']);
    if(isset($_SESSION[DOKU_COOKIE]['auth']['pass']))
        unset($_SESSION[DOKU_COOKIE]['auth']['pass']);
    if(isset($_SESSION[DOKU_COOKIE]['auth']['info']))
        unset($_SESSION[DOKU_COOKIE]['auth']['info']);
    if(!$keepbc && isset($_SESSION[DOKU_COOKIE]['bc']))
        unset($_SESSION[DOKU_COOKIE]['bc']);
    if(isset($_SERVER['REMOTE_USER']))
        unset($_SERVER['REMOTE_USER']);
    $USERINFO=null; //FIXME

    if (version_compare(PHP_VERSION, '5.2.0', '>')) {
        setcookie(DOKU_COOKIE,'',time()-600000,DOKU_REL,'',($conf['securecookie'] && is_ssl()),true);
    }else{
        setcookie(DOKU_COOKIE,'',time()-600000,DOKU_REL,'',($conf['securecookie'] && is_ssl()));
    }

    if($auth) $auth->logOff();
}

/**
 * Check if a user is a manager
 *
 * Should usually be called without any parameters to check the current
 * user.
 *
 * The info is available through $INFO['ismanager'], too
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    auth_isadmin
 * @param  string user      - Username
 * @param  array  groups    - List of groups the user is in
 * @param  bool   adminonly - when true checks if user is admin
 */
function auth_ismanager($user=null,$groups=null,$adminonly=false){
    global $conf;
    global $USERINFO;
    global $auth;

    if (!$auth) return false;
    if(is_null($user)) {
        if (!isset($_SERVER['REMOTE_USER'])) {
            return false;
        } else {
            $user = $_SERVER['REMOTE_USER'];
        }
    }
    $user = trim($auth->cleanUser($user));
    if($user === '') return false;
    if(is_null($groups)) $groups = (array) $USERINFO['grps'];
    $groups = array_map(array($auth,'cleanGroup'),$groups);
    $user   = auth_nameencode($user);

    // check username against superuser and manager
    $superusers = explode(',', $conf['superuser']);
    $superusers = array_unique($superusers);
    $superusers = array_map('trim', $superusers);
    $superusers = array_filter($superusers);
    // prepare an array containing only true values for array_map call
    $alltrue = array_fill(0, count($superusers), true);
    $superusers = array_map('auth_nameencode', $superusers, $alltrue);

    // case insensitive?
    if(!$auth->isCaseSensitive()){
        $superusers = array_map('utf8_strtolower',$superusers);
        $user       = utf8_strtolower($user);
    }

    // check user match
    if(in_array($user, $superusers)) return true;

    // check managers
    if(!$adminonly){
        $managers = explode(',', $conf['manager']);
        $managers = array_unique($managers);
        $managers = array_map('trim', $managers);
        $managers = array_filter($managers);
        // prepare an array containing only true values for array_map call
        $alltrue = array_fill(0, count($managers), true);
        $managers = array_map('auth_nameencode', $managers, $alltrue);
        if(!$auth->isCaseSensitive()) $managers = array_map('utf8_strtolower',$managers);
        if(in_array($user, $managers)) return true;
    }

    // check user's groups against superuser and manager
    if (!empty($groups)) {

        //prepend groups with @ and nameencode
        $cnt = count($groups);
        for($i=0; $i<$cnt; $i++){
            $groups[$i] = '@'.auth_nameencode($groups[$i]);
            if(!$auth->isCaseSensitive()){
                $groups[$i] = utf8_strtolower($groups[$i]);
            }
        }

        // check groups against superuser and manager
        foreach($superusers as $supu)
            if(in_array($supu, $groups)) return true;
        if(!$adminonly){
            foreach($managers as $mana)
                if(in_array($mana, $groups)) return true;
        }
    }

    return false;
}

/**
 * Check if a user is admin
 *
 * Alias to auth_ismanager with adminonly=true
 *
 * The info is available through $INFO['isadmin'], too
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see auth_ismanager
 */
function auth_isadmin($user=null,$groups=null){
    return auth_ismanager($user,$groups,true);
}

/**
 * Convinience function for auth_aclcheck()
 *
 * This checks the permissions for the current user
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param  string  $id  page ID (needs to be resolved and cleaned)
 * @return int          permission level
 */
function auth_quickaclcheck($id){
    global $conf;
    global $USERINFO;
    # if no ACL is used always return upload rights
    if(!$conf['useacl']) return AUTH_UPLOAD;
    return auth_aclcheck($id,$_SERVER['REMOTE_USER'],$USERINFO['grps']);
}

/**
 * Returns the maximum rights a user has for
 * the given ID or its namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @param  string  $id     page ID (needs to be resolved and cleaned)
 * @param  string  $user   Username
 * @param  array   $groups Array of groups the user is in
 * @return int             permission level
 */
function auth_aclcheck($id,$user,$groups){
    global $conf;
    global $AUTH_ACL;
    global $auth;

    // if no ACL is used always return upload rights
    if(!$conf['useacl']) return AUTH_UPLOAD;
    if (!$auth) return AUTH_NONE;

    //make sure groups is an array
    if(!is_array($groups)) $groups = array();

    //if user is superuser or in superusergroup return 255 (acl_admin)
    if(auth_isadmin($user,$groups)) { return AUTH_ADMIN; }

    $ci = '';
    if(!$auth->isCaseSensitive()) $ci = 'ui';

    $user = $auth->cleanUser($user);
    $groups = array_map(array($auth,'cleanGroup'),(array)$groups);
    $user = auth_nameencode($user);

    //prepend groups with @ and nameencode
    $cnt = count($groups);
    for($i=0; $i<$cnt; $i++){
        $groups[$i] = '@'.auth_nameencode($groups[$i]);
    }

    $ns    = getNS($id);
    $perm  = -1;

    if($user || count($groups)){
        //add ALL group
        $groups[] = '@ALL';
        //add User
        if($user) $groups[] = $user;
        //build regexp
        $regexp   = join('|',$groups);
    }else{
        $regexp = '@ALL';
    }

    //check exact match first
    $matches = preg_grep('/^'.preg_quote($id,'/').'\s+('.$regexp.')\s+/'.$ci,$AUTH_ACL);
    if(count($matches)){
        foreach($matches as $match){
            $match = preg_replace('/#.*$/','',$match); //ignore comments
            $acl   = preg_split('/\s+/',$match);
            if($acl[2] > AUTH_DELETE) $acl[2] = AUTH_DELETE; //no admins in the ACL!
            if($acl[2] > $perm){
                $perm = $acl[2];
            }
        }
        if($perm > -1){
            //we had a match - return it
            return $perm;
        }
    }

    //still here? do the namespace checks
    if($ns){
        $path = $ns.':*';
    }else{
        $path = '*'; //root document
    }

    do{
        $matches = preg_grep('/^'.preg_quote($path,'/').'\s+('.$regexp.')\s+/'.$ci,$AUTH_ACL);
        if(count($matches)){
            foreach($matches as $match){
                $match = preg_replace('/#.*$/','',$match); //ignore comments
                $acl   = preg_split('/\s+/',$match);
                if($acl[2] > AUTH_DELETE) $acl[2] = AUTH_DELETE; //no admins in the ACL!
                if($acl[2] > $perm){
                    $perm = $acl[2];
                }
            }
            //we had a match - return it
            return $perm;
        }

        //get next higher namespace
        $ns   = getNS($ns);

        if($path != '*'){
            $path = $ns.':*';
            if($path == ':*') $path = '*';
        }else{
            //we did this already
            //looks like there is something wrong with the ACL
            //break here
            msg('No ACL setup yet! Denying access to everyone.');
            return AUTH_NONE;
        }
    }while(1); //this should never loop endless

    //still here? return no permissions
    return AUTH_NONE;
}

/**
 * Encode ASCII special chars
 *
 * Some auth backends allow special chars in their user and groupnames
 * The special chars are encoded with this function. Only ASCII chars
 * are encoded UTF-8 multibyte are left as is (different from usual
 * urlencoding!).
 *
 * Decoding can be done with rawurldecode
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @see rawurldecode()
 */
function auth_nameencode($name,$skip_group=false){
    global $cache_authname;
    $cache =& $cache_authname;
    $name  = (string) $name;

    // never encode wildcard FS#1955
    if($name == '%USER%') return $name;

    if (!isset($cache[$name][$skip_group])) {
        if($skip_group && $name{0} =='@'){
            $cache[$name][$skip_group] = '@'.preg_replace('/([\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f])/e',
                    "'%'.dechex(ord(substr('\\1',-1)))",substr($name,1));
        }else{
            $cache[$name][$skip_group] = preg_replace('/([\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f])/e',
                    "'%'.dechex(ord(substr('\\1',-1)))",$name);
        }
    }

    return $cache[$name][$skip_group];
}

/**
 * Create a pronouncable password
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @link    http://www.phpbuilder.com/annotate/message.php3?id=1014451
 *
 * @return string  pronouncable password
 */
function auth_pwgen(){
    $pw = '';
    $c  = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
    $v  = 'aeiou';              //vowels
    $a  = $c.$v;                //both

    //use two syllables...
    for($i=0;$i < 2; $i++){
        $pw .= $c[rand(0, strlen($c)-1)];
        $pw .= $v[rand(0, strlen($v)-1)];
        $pw .= $a[rand(0, strlen($a)-1)];
    }
    //... and add a nice number
    $pw .= rand(10,99);

    return $pw;
}

/**
 * Sends a password to the given user
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @return bool  true on success
 */
function auth_sendPassword($user,$password){
    global $conf;
    global $lang;
    global $auth;
    if (!$auth) return false;

    $hdrs  = '';
    $user     = $auth->cleanUser($user);
    $userinfo = $auth->getUserData($user);

    if(!$userinfo['mail']) return false;

    $text = rawLocale('password');
    $text = str_replace('@DOKUWIKIURL@',DOKU_URL,$text);
    $text = str_replace('@FULLNAME@',$userinfo['name'],$text);
    $text = str_replace('@LOGIN@',$user,$text);
    $text = str_replace('@PASSWORD@',$password,$text);
    $text = str_replace('@TITLE@',$conf['title'],$text);

    return mail_send($userinfo['name'].' <'.$userinfo['mail'].'>',
            $lang['regpwmail'],
            $text,
            $conf['mailfrom']);
}

/**
 * Register a new user
 *
 * This registers a new user - Data is read directly from $_POST
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @return bool  true on success, false on any error
 */
function register(){
    global $lang;
    global $conf;
    global $auth;

    if (!$auth) return false;
    if(!$_POST['save']) return false;
    if(!$auth->canDo('addUser')) return false;

    //clean username
    $_POST['login'] = trim($auth->cleanUser($_POST['login']));

    //clean fullname and email
    $_POST['fullname'] = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/','',$_POST['fullname']));
    $_POST['email']    = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/','',$_POST['email']));

    if( empty($_POST['login']) ||
        empty($_POST['fullname']) ||
        empty($_POST['email']) ){
        msg($lang['regmissing'],-1);
        return false;
    }

    if ($conf['autopasswd']) {
        $pass = auth_pwgen();                // automatically generate password
    } elseif (empty($_POST['pass']) ||
            empty($_POST['passchk'])) {
        msg($lang['regmissing'], -1);        // complain about missing passwords
        return false;
    } elseif ($_POST['pass'] != $_POST['passchk']) {
        msg($lang['regbadpass'], -1);      // complain about misspelled passwords
        return false;
    } else {
        $pass = $_POST['pass'];              // accept checked and valid password
    }

    //check mail
    if(!mail_isvalid($_POST['email'])){
        msg($lang['regbadmail'],-1);
        return false;
    }

    //okay try to create the user
    if(!$auth->triggerUserMod('create', array($_POST['login'],$pass,$_POST['fullname'],$_POST['email']))){
        msg($lang['reguexists'],-1);
        return false;
    }

    // create substitutions for use in notification email
    $substitutions = array(
            'NEWUSER' => $_POST['login'],
            'NEWNAME' => $_POST['fullname'],
            'NEWEMAIL' => $_POST['email'],
            );

    if (!$conf['autopasswd']) {
        msg($lang['regsuccess2'],1);
        notify('', 'register', '', $_POST['login'], false, $substitutions);
        return true;
    }

    // autogenerated password? then send him the password
    if (auth_sendPassword($_POST['login'],$pass)){
        msg($lang['regsuccess'],1);
        notify('', 'register', '', $_POST['login'], false, $substitutions);
        return true;
    }else{
        msg($lang['regmailfail'],-1);
        return false;
    }
}

/**
 * Update user profile
 *
 * @author    Christopher Smith <chris@jalakai.co.uk>
 */
function updateprofile() {
    global $conf;
    global $INFO;
    global $lang;
    global $auth;

    if (!$auth) return false;
    if(empty($_POST['save'])) return false;
    if(!checkSecurityToken()) return false;

    // should not be able to get here without Profile being possible...
    if(!$auth->canDo('Profile')) {
        msg($lang['profna'],-1);
        return false;
    }

    if ($_POST['newpass'] != $_POST['passchk']) {
        msg($lang['regbadpass'], -1);      // complain about misspelled passwords
        return false;
    }

    //clean fullname and email
    $_POST['fullname'] = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/','',$_POST['fullname']));
    $_POST['email']    = trim(preg_replace('/[\x00-\x1f:<>&%,;]+/','',$_POST['email']));

    if ((empty($_POST['fullname']) && $auth->canDo('modName')) ||
        (empty($_POST['email']) && $auth->canDo('modMail'))) {
        msg($lang['profnoempty'],-1);
        return false;
    }

    if (!mail_isvalid($_POST['email']) && $auth->canDo('modMail')){
        msg($lang['regbadmail'],-1);
        return false;
    }

    if ($_POST['fullname'] != $INFO['userinfo']['name'] && $auth->canDo('modName')) $changes['name'] = $_POST['fullname'];
    if ($_POST['email'] != $INFO['userinfo']['mail'] && $auth->canDo('modMail')) $changes['mail'] = $_POST['email'];
    if (!empty($_POST['newpass']) && $auth->canDo('modPass')) $changes['pass'] = $_POST['newpass'];

    if (!count($changes)) {
        msg($lang['profnochange'], -1);
        return false;
    }

    if ($conf['profileconfirm']) {
        if (!$auth->checkPass($_SERVER['REMOTE_USER'], $_POST['oldpass'])) {
            msg($lang['badlogin'],-1);
            return false;
        }
    }

    if ($result = $auth->triggerUserMod('modify', array($_SERVER['REMOTE_USER'], $changes))) {
        // update cookie and session with the changed data
        $cookie = base64_decode($_COOKIE[DOKU_COOKIE]);
        list($user,$sticky,$pass) = explode('|',$cookie,3);
        if ($changes['pass']) $pass = PMA_blowfish_encrypt($changes['pass'],auth_cookiesalt());

        auth_setCookie($_SERVER['REMOTE_USER'],$pass,(bool)$sticky);
        return true;
    }
}

/**
 * Send a  new password
 *
 * This function handles both phases of the password reset:
 *
 *   - handling the first request of password reset
 *   - validating the password reset auth token
 *
 * @author Benoit Chesneau <benoit@bchesneau.info>
 * @author Chris Smith <chris@jalakai.co.uk>
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @return bool true on success, false on any error
 */
function act_resendpwd(){
    global $lang;
    global $conf;
    global $auth;

    if(!actionOK('resendpwd')) return false;
    if (!$auth) return false;

    // should not be able to get here without modPass being possible...
    if(!$auth->canDo('modPass')) {
        msg($lang['resendna'],-1);
        return false;
    }

    $token = preg_replace('/[^a-f0-9]+/','',$_REQUEST['pwauth']);

    if($token){
        // we're in token phase

        $tfile = $conf['cachedir'].'/'.$token{0}.'/'.$token.'.pwauth';
        if(!@file_exists($tfile)){
            msg($lang['resendpwdbadauth'],-1);
            return false;
        }
        $user = io_readfile($tfile);
        @unlink($tfile);
        $userinfo = $auth->getUserData($user);
        if(!$userinfo['mail']) {
            msg($lang['resendpwdnouser'], -1);
            return false;
        }

        $pass = auth_pwgen();
        if (!$auth->triggerUserMod('modify', array($user,array('pass' => $pass)))) {
            msg('error modifying user data',-1);
            return false;
        }

        if (auth_sendPassword($user,$pass)) {
            msg($lang['resendpwdsuccess'],1);
        } else {
            msg($lang['regmailfail'],-1);
        }
        return true;

    } else {
        // we're in request phase

        if(!$_POST['save']) return false;

        if (empty($_POST['login'])) {
            msg($lang['resendpwdmissing'], -1);
            return false;
        } else {
            $user = trim($auth->cleanUser($_POST['login']));
        }

        $userinfo = $auth->getUserData($user);
        if(!$userinfo['mail']) {
            msg($lang['resendpwdnouser'], -1);
            return false;
        }

        // generate auth token
        $token = md5(auth_cookiesalt().$user); //secret but user based
        $tfile = $conf['cachedir'].'/'.$token{0}.'/'.$token.'.pwauth';
        $url = wl('',array('do'=>'resendpwd','pwauth'=>$token),true,'&');

        io_saveFile($tfile,$user);

        $text = rawLocale('pwconfirm');
        $text = str_replace('@DOKUWIKIURL@',DOKU_URL,$text);
        $text = str_replace('@FULLNAME@',$userinfo['name'],$text);
        $text = str_replace('@LOGIN@',$user,$text);
        $text = str_replace('@TITLE@',$conf['title'],$text);
        $text = str_replace('@CONFIRM@',$url,$text);

        if(mail_send($userinfo['name'].' <'.$userinfo['mail'].'>',
                     $lang['regpwmail'],
                     $text,
                     $conf['mailfrom'])){
            msg($lang['resendpwdconfirm'],1);
        }else{
            msg($lang['regmailfail'],-1);
        }
        return true;
    }

    return false; // never reached
}

/**
 * Encrypts a password using the given method and salt
 *
 * If the selected method needs a salt and none was given, a random one
 * is chosen.
 *
 * The following methods are understood:
 *
 *   smd5  - Salted MD5 hashing
 *   apr1  - Apache salted MD5 hashing
 *   md5   - Simple MD5 hashing
 *   sha1  - SHA1 hashing
 *   ssha  - Salted SHA1 hashing
 *   crypt - Unix crypt
 *   mysql - MySQL password (old method)
 *   my411 - MySQL 4.1.1 password
 *   kmd5  - Salted MD5 hashing as used by UNB
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return  string  The crypted password
 */
function auth_cryptPassword($clear,$method='',$salt=null){
    global $conf;
    if(empty($method)) $method = $conf['passcrypt'];

    //prepare a salt
    if(is_null($salt)) $salt = md5(uniqid(rand(), true));

    switch(strtolower($method)){
        case 'smd5':
            if(defined('CRYPT_MD5') && CRYPT_MD5) return crypt($clear,'$1$'.substr($salt,0,8).'$');
            // when crypt can't handle SMD5, falls through to pure PHP implementation
            $magic = '1';
        case 'apr1':
            //from http://de.php.net/manual/en/function.crypt.php#73619 comment by <mikey_nich at hotmail dot com>
            if(!isset($magic)) $magic = 'apr1';
            $salt = substr($salt,0,8);
            $len = strlen($clear);
            $text = $clear.'$'.$magic.'$'.$salt;
            $bin = pack("H32", md5($clear.$salt.$clear));
            for($i = $len; $i > 0; $i -= 16) {
                $text .= substr($bin, 0, min(16, $i));
            }
            for($i = $len; $i > 0; $i >>= 1) {
                $text .= ($i & 1) ? chr(0) : $clear{0};
            }
            $bin = pack("H32", md5($text));
            for($i = 0; $i < 1000; $i++) {
                $new = ($i & 1) ? $clear : $bin;
                if ($i % 3) $new .= $salt;
                if ($i % 7) $new .= $clear;
                $new .= ($i & 1) ? $bin : $clear;
                $bin = pack("H32", md5($new));
            }
            $tmp = '';
            for ($i = 0; $i < 5; $i++) {
                $k = $i + 6;
                $j = $i + 12;
                if ($j == 16) $j = 5;
                $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
            }
            $tmp = chr(0).chr(0).$bin[11].$tmp;
            $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
                    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
            return '$'.$magic.'$'.$salt.'$'.$tmp;
        case 'md5':
            return md5($clear);
        case 'sha1':
            return sha1($clear);
        case 'ssha':
            $salt=substr($salt,0,4);
            return '{SSHA}'.base64_encode(pack("H*", sha1($clear.$salt)).$salt);
        case 'crypt':
            return crypt($clear,substr($salt,0,2));
        case 'mysql':
            //from http://www.php.net/mysql comment by <soren at byu dot edu>
            $nr=0x50305735;
            $nr2=0x12345671;
            $add=7;
            $charArr = preg_split("//", $clear);
            foreach ($charArr as $char) {
                if (($char == '') || ($char == ' ') || ($char == '\t')) continue;
                $charVal = ord($char);
                $nr ^= ((($nr & 63) + $add) * $charVal) + ($nr << 8);
                $nr2 += ($nr2 << 8) ^ $nr;
                $add += $charVal;
            }
            return sprintf("%08x%08x", ($nr & 0x7fffffff), ($nr2 & 0x7fffffff));
        case 'my411':
            return '*'.sha1(pack("H*", sha1($clear)));
        case 'kmd5':
            $key = substr($salt, 16, 2);
            $hash1 = strtolower(md5($key . md5($clear)));
            $hash2 = substr($hash1, 0, 16) . $key . substr($hash1, 16);
            return $hash2;
        default:
            msg("Unsupported crypt method $method",-1);
    }
}

/**
 * Verifies a cleartext password against a crypted hash
 *
 * The method and salt used for the crypted hash is determined automatically
 * then the clear text password is crypted using the same method. If both hashs
 * match true is is returned else false
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return  bool
 */
function auth_verifyPassword($clear,$crypt){
    $method='';
    $salt='';

    //determine the used method and salt
    $len = strlen($crypt);
    if(preg_match('/^\$1\$([^\$]{0,8})\$/',$crypt,$m)){
        $method = 'smd5';
        $salt   = $m[1];
    }elseif(preg_match('/^\$apr1\$([^\$]{0,8})\$/',$crypt,$m)){
        $method = 'apr1';
        $salt   = $m[1];
    }elseif(substr($crypt,0,6) == '{SSHA}'){
        $method = 'ssha';
        $salt   = substr(base64_decode(substr($crypt, 6)),20);
    }elseif($len == 32){
        $method = 'md5';
    }elseif($len == 40){
        $method = 'sha1';
    }elseif($len == 16){
        $method = 'mysql';
    }elseif($len == 41 && $crypt[0] == '*'){
        $method = 'my411';
    }elseif($len == 34){
        $method = 'kmd5';
        $salt   = $crypt;
    }else{
        $method = 'crypt';
        $salt   = substr($crypt,0,2);
    }

    //crypt and compare
    if(auth_cryptPassword($clear,$method,$salt) === $crypt){
        return true;
    }
    return false;
}

/**
 * Set the authentication cookie and add user identification data to the session
 *
 * @param string  $user       username
 * @param string  $pass       encrypted password
 * @param bool    $sticky     whether or not the cookie will last beyond the session
 */
function auth_setCookie($user,$pass,$sticky) {
    global $conf;
    global $auth;
    global $USERINFO;

    if (!$auth) return false;
    $USERINFO = $auth->getUserData($user);

    // set cookie
    $cookie = base64_encode($user).'|'.((int) $sticky).'|'.base64_encode($pass);
    $time = $sticky ? (time()+60*60*24*365) : 0; //one year
    if (version_compare(PHP_VERSION, '5.2.0', '>')) {
        setcookie(DOKU_COOKIE,$cookie,$time,DOKU_REL,'',($conf['securecookie'] && is_ssl()),true);
    }else{
        setcookie(DOKU_COOKIE,$cookie,$time,DOKU_REL,'',($conf['securecookie'] && is_ssl()));
    }
    // set session
    $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
    $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
    $_SESSION[DOKU_COOKIE]['auth']['buid'] = auth_browseruid();
    $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
    $_SESSION[DOKU_COOKIE]['auth']['time'] = time();
}

/**
 * Returns the user, (encrypted) password and sticky bit from cookie
 *
 * @returns array
 */
function auth_getCookie(){
    if (!isset($_COOKIE[DOKU_COOKIE])) {
        return array(null, null, null);
    }
    list($user,$sticky,$pass) = explode('|',$_COOKIE[DOKU_COOKIE],3);
    $sticky = (bool) $sticky;
    $pass   = base64_decode($pass);
    $user   = base64_decode($user);
    return array($user,$sticky,$pass);
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
