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

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/io.php');

  // some ACL level defines
  define('AUTH_NONE',0);
  define('AUTH_READ',1);
  define('AUTH_EDIT',2);
  define('AUTH_CREATE',4);
  define('AUTH_UPLOAD',8);
  define('AUTH_DELETE',16);
  define('AUTH_ADMIN',255);

  global $conf;

  if($conf['useacl']){
    require_once(DOKU_INC.'inc/blowfish.php');
    require_once(DOKU_INC.'inc/mail.php');

    global $auth;

    // load the the backend auth functions and instantiate the auth object
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
  }

  // do the login either by cookie or provided credentials
  if($conf['useacl']){
    if($auth){
      if (!isset($_REQUEST['u'])) $_REQUEST['u'] = '';
      if (!isset($_REQUEST['p'])) $_REQUEST['p'] = '';
      if (!isset($_REQUEST['r'])) $_REQUEST['r'] = '';

      // if no credentials were given try to use HTTP auth (for SSO)
      if(empty($_REQUEST['u']) && empty($_COOKIE[DOKU_COOKIE]) && !empty($_SERVER['PHP_AUTH_USER'])){
        $_REQUEST['u'] = $_SERVER['PHP_AUTH_USER'];
        $_REQUEST['p'] = $_SERVER['PHP_AUTH_PW'];
      }

      // external trust mechanism in place?
      if(!is_null($auth) && $auth->canDo('external')){
        $auth->trustExternal($_REQUEST['u'],$_REQUEST['p'],$_REQUEST['r']);
      }else{
        auth_login($_REQUEST['u'],$_REQUEST['p'],$_REQUEST['r']);
      }
    }

    //load ACL into a global array
    global $AUTH_ACL;
    if(is_readable(DOKU_CONF.'acl.auth.php')){
      $AUTH_ACL = file(DOKU_CONF.'acl.auth.php');
      if(isset($_SERVER['REMOTE_USER'])){
        $AUTH_ACL = str_replace('@USER@',$_SERVER['REMOTE_USER'],$AUTH_ACL);
      }
    }else{
      $AUTH_ACL = array();
    }
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

  if(!empty($user)){
    //usual login
    if ($auth->checkPass($user,$pass)){
      // make logininfo globally available
      $_SERVER['REMOTE_USER'] = $user;
      $USERINFO = $auth->getUserData($user);

      // set cookie
      $pass   = PMA_blowfish_encrypt($pass,auth_cookiesalt());
      $cookie = base64_encode("$user|$sticky|$pass");
      if($sticky) $time = time()+60*60*24*365; //one year
      setcookie(DOKU_COOKIE,$cookie,$time,DOKU_REL);

      // set session
      $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
      $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
      $_SESSION[DOKU_COOKIE]['auth']['buid'] = auth_browseruid();
      $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
      $_SESSION[DOKU_COOKIE]['auth']['time'] = time();

      return true;
    }else{
      //invalid credentials - log off
      if(!$silent) msg($lang['badlogin'],-1);
      auth_logoff();
      return false;
    }
  }else{
    // read cookie information
    $cookie = base64_decode($_COOKIE[DOKU_COOKIE]);
    list($user,$sticky,$pass) = split('\|',$cookie,3);
    // get session info
    $session = $_SESSION[DOKU_COOKIE]['auth'];
    if($user && $pass){
      // we got a cookie - see if we can trust it
      if(isset($session) &&
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
  auth_logoff();
  return false;
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
  $uid  = '';
  $uid .= $_SERVER['HTTP_USER_AGENT'];
  $uid .= $_SERVER['HTTP_ACCEPT_ENCODING'];
  $uid .= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
  $uid .= $_SERVER['HTTP_ACCEPT_CHARSET'];
  $uid .= substr($_SERVER['REMOTE_ADDR'],0,strpos($_SERVER['REMOTE_ADDR'],'.'));
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
 * This clears all authenticationdata and thus log the user
 * off
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_logoff(){
  global $conf;
  global $USERINFO;
  global $INFO, $ID;
  global $auth;

  if(isset($_SESSION[DOKU_COOKIE]['auth']['user']))
    unset($_SESSION[DOKU_COOKIE]['auth']['user']);
  if(isset($_SESSION[DOKU_COOKIE]['auth']['pass']))
    unset($_SESSION[DOKU_COOKIE]['auth']['pass']);
  if(isset($_SESSION[DOKU_COOKIE]['auth']['info']))
    unset($_SESSION[DOKU_COOKIE]['auth']['info']);
  if(isset($_SERVER['REMOTE_USER']))
    unset($_SERVER['REMOTE_USER']);
  $USERINFO=null; //FIXME
  setcookie(DOKU_COOKIE,'',time()-600000,DOKU_REL);

  if($auth && $auth->canDo('logoff')){
    $auth->logOff();
  }
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

  if(!$conf['useacl']) return false;
  if(is_null($user))   $user   = $_SERVER['REMOTE_USER'];
  if(is_null($groups)) $groups = $USERINFO['grps'];
  $user   = auth_nameencode($user);

  // check username against superuser and manager
  if(auth_nameencode($conf['superuser']) == $user) return true;
  if(!$adminonly){
    if(auth_nameencode($conf['manager']) == $user) return true;
  }

  //prepend groups with @ and nameencode
  $cnt = count($groups);
  for($i=0; $i<$cnt; $i++){
    $groups[$i] = '@'.auth_nameencode($groups[$i]);
  }

  // check groups against superuser and manager
  if(in_array(auth_nameencode($conf['superuser'],true), $groups)) return true;
  if(!$adminonly){
    if(in_array(auth_nameencode($conf['manager'],true), $groups)) return true;
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
 * @param  string  $id  page ID
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
 * @param  string  $id     page ID
 * @param  string  $user   Username
 * @param  array   $groups Array of groups the user is in
 * @return int             permission level
 */
function auth_aclcheck($id,$user,$groups){
  global $conf;
  global $AUTH_ACL;

  # if no ACL is used always return upload rights
  if(!$conf['useacl']) return AUTH_UPLOAD;

  $user = auth_nameencode($user);

  //if user is superuser return 255 (acl_admin)
  if(auth_nameencode($conf['superuser']) == $user) { return AUTH_ADMIN; }

  //make sure groups is an array
  if(!is_array($groups)) $groups = array();

  //prepend groups with @ and nameencode
  $cnt = count($groups);
  for($i=0; $i<$cnt; $i++){
    $groups[$i] = '@'.auth_nameencode($groups[$i]);
  }
  //if user is in superuser group return 255 (acl_admin)
  if(in_array(auth_nameencode($conf['superuser'],true), $groups)) { return AUTH_ADMIN; }

  $ns    = getNS($id);
  $perm  = -1;

  if($user){
    //add ALL group
    $groups[] = '@ALL';
    //add User
    $groups[] = $user;
    //build regexp
    $regexp   = join('|',$groups);
  }else{
    $regexp = '@ALL';
  }

  //check exact match first
  $matches = preg_grep('/^'.preg_quote($id,'/').'\s+('.$regexp.')\s+/',$AUTH_ACL);
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
    $path = $ns.':\*';
  }else{
    $path = '\*'; //root document
  }

  do{
    $matches = preg_grep('/^'.$path.'\s+('.$regexp.')\s+/',$AUTH_ACL);
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

    if($path != '\*'){
      $path = $ns.':\*';
      if($path == ':\*') $path = '\*';
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

  if (!isset($cache[$name][$skip_group])) {
    if($skip_group && $name{0} =='@'){
      $cache[$name][$skip_group] = '@'.preg_replace('/([\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f])/e',
                                                    "'%'.dechex(ord('\\1'))",substr($name,1));
    }else{
      $cache[$name][$skip_group] = preg_replace('/([\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f])/e',
                                                "'%'.dechex(ord('\\1'))",$name);
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

  $hdrs  = '';
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

  if(!$_POST['save']) return false;
  if(!$auth->canDo('addUser')) return false;

  //clean username
  $_POST['login'] = preg_replace('/.*:/','',$_POST['login']);
  $_POST['login'] = cleanID($_POST['login']);
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
  if(!$auth->createUser($_POST['login'],$pass,$_POST['fullname'],$_POST['email'])){
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

  if(empty($_POST['save'])) return false;

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

  if (empty($_POST['fullname']) || empty($_POST['email'])) {
    msg($lang['profnoempty'],-1);
    return false;
  }

  if (!mail_isvalid($_POST['email'])){
    msg($lang['regbadmail'],-1);
    return false;
  }

  if ($_POST['fullname'] != $INFO['userinfo']['name']) $changes['name'] = $_POST['fullname'];
  if ($_POST['email']    != $INFO['userinfo']['mail']) $changes['mail'] = $_POST['email'];
  if (!empty($_POST['newpass']))  $changes['pass'] = $_POST['newpass'];

  if (!count($changes)) {
    msg($lang['profnochange'], -1);
    return false;
  }

  if ($conf['profileconfirm']) {
      if (!auth_verifyPassword($_POST['oldpass'],$INFO['userinfo']['pass'])) {
      msg($lang['badlogin'],-1);
      return false;
    }
  }

  return $auth->modifyUser($_SERVER['REMOTE_USER'], $changes);
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
        if (!$auth->modifyUser($user,array('pass' => $pass))) {
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
            $_POST['login'] = preg_replace('/.*:/','',$_POST['login']);
            $user = cleanID($_POST['login']);
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
 * Uses a regular expresion to check if a given mail address is valid
 *
 * May not be completly RFC conform!
 *
 * @link    http://www.webmasterworld.com/forum88/135.htm
 *
 * @param   string $email the address to check
 * @return  bool          true if address is valid
 */
function isvalidemail($email){
  return eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$", $email);
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
 *   md5   - Simple MD5 hashing
 *   sha1  - SHA1 hashing
 *   ssha  - Salted SHA1 hashing
 *   crypt - Unix crypt
 *   mysql - MySQL password (old method)
 *   my411 - MySQL 4.1.1 password
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return  string  The crypted password
 */
function auth_cryptPassword($clear,$method='',$salt=''){
  global $conf;
  if(empty($method)) $method = $conf['passcrypt'];

  //prepare a salt
  if(empty($salt)) $salt = md5(uniqid(rand(), true));

  switch(strtolower($method)){
    case 'smd5':
        return crypt($clear,'$1$'.substr($salt,0,8).'$');
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
  if(substr($crypt,0,3) == '$1$'){
    $method = 'smd5';
    $salt   = substr($crypt,3,8);
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

//Setup VIM: ex: et ts=2 enc=utf-8 :
