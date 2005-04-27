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
  require_once(DOKU_INC.'inc/blowfish.php');
  require_once(DOKU_INC.'inc/mail.php');
  // load the the auth functions
  require_once(DOKU_INC.'inc/auth_'.$conf['authtype'].'.php');

  // some ACL level defines
  define('AUTH_NONE',0);
  define('AUTH_READ',1);
  define('AUTH_EDIT',2);
  define('AUTH_CREATE',4);
  define('AUTH_UPLOAD',8);
  define('AUTH_ADMIN',255);

  if($conf['useacl']){
    auth_login($_REQUEST['u'],$_REQUEST['p'],$_REQUEST['r']);
    //load ACL into a global array
    $AUTH_ACL = file('conf/acl.auth');
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
 * @return  bool             true on successful auth
*/
function auth_login($user,$pass,$sticky=false){
  global $USERINFO;
  global $conf;
  global $lang;
  $sticky ? $sticky = true : $sticky = false; //sanity check

  if(isset($user)){
    //usual login
    if (auth_checkPass($user,$pass)){
      // make logininfo globally available
      $_SERVER['REMOTE_USER'] = $user;
      $USERINFO = auth_getUserData($user); //FIXME move all references to session 

      // set cookie
      $pass   = PMA_blowfish_encrypt($pass,auth_cookiesalt());
      $cookie = base64_encode("$user|$sticky|$pass");
      if($sticky) $time = time()+60*60*24*365; //one year
      setcookie('DokuWikiAUTH',$cookie,$time);

      // set session
      $_SESSION[$conf['title']]['auth']['user'] = $user;
      $_SESSION[$conf['title']]['auth']['pass'] = $pass;
      $_SESSION[$conf['title']]['auth']['buid'] = auth_browseruid();
      $_SESSION[$conf['title']]['auth']['info'] = $USERINFO;
      return true;
    }else{
      //invalid credentials - log off
      msg($lang['badlogin'],-1);
      auth_logoff();
      return false;
    }
  }else{
    // read cookie information
    $cookie = base64_decode($_COOKIE['DokuWikiAUTH']);
    list($user,$sticky,$pass) = split('\|',$cookie,3);
    // get session info
    $session = $_SESSION[$conf['title']]['auth'];

    if($user && $pass){
      // we got a cookie - see if we can trust it
      if(isset($session) &&
        ($session['user'] == $user) &&
        ($session['pass'] == $pass) &&  //still crypted
        ($session['buid'] == auth_browseruid()) ){
        // he has session, cookie and browser right - let him in
        $_SERVER['REMOTE_USER'] = $user;
        $USERINFO = $session['info']; //FIXME move all references to session
        return true;
      }
      // no we don't trust it yet - recheck pass
      $pass = PMA_blowfish_decrypt($pass,auth_cookiesalt());
      return auth_login($user,$pass,$sticky);
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
 * cookies from $conf['datadir'].'/_cache/_htcookiesalt'
 * if no such file is found a random key is created and
 * and stored in this file.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 *
 * @return  string
 */
function auth_cookiesalt(){
  global $conf;
  $file = $conf['datadir'].'/_cache/_htcookiesalt';
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
  unset($_SESSION[$conf['title']]['auth']['user']);
  unset($_SESSION[$conf['title']]['auth']['pass']);
  unset($_SESSION[$conf['title']]['auth']['info']);
  unset($_SERVER['REMOTE_USER']);
  $USERINFO=null; //FIXME
  setcookie('DokuWikiAUTH','',time()-3600);
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
  
  //if user is superuser return 255 (acl_admin)
  if($conf['superuser'] == $user) { return AUTH_ADMIN; }

  //make sure groups is an array
  if(!is_array($groups)) $groups = array();

  //prepend groups with @
  $cnt = count($groups);
  for($i=0; $i<$cnt; $i++){
    $groups[$i] = '@'.$groups[$i];
  }
  //if user is in superuser group return 255 (acl_admin)
  if(in_array($conf['superuser'], $groups)) { return AUTH_ADMIN; }

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
  $matches = preg_grep('/^'.$id.'\s+('.$regexp.')\s+/',$AUTH_ACL);
  if(count($matches)){
    foreach($matches as $match){
      $match = preg_replace('/#.*$/','',$match); //ignore comments
      $acl   = preg_split('/\s+/',$match);
      if($acl[2] > AUTH_UPLOAD) $acl[2] = AUTH_UPLOAD; //no admins in the ACL!
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
        if($acl[2] > AUTH_UPLOAD) $acl[2] = AUTH_UPLOAD; //no admins in the ACL!
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
      return $perm;
    }
  }while(1); //this should never loop endless

  //still here? return no permissions
  return AUTH_NONE;
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
  $hdrs  = '';
  $userinfo = auth_getUserData($user);

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

  if(!$_POST['save']) return false;
  if(!$conf['openregister']) return false;

  //clean username
  $_POST['login'] = preg_replace('/.*:/','',$_POST['login']);
  $_POST['login'] = cleanID($_POST['login']);
  //clean fullname and email
  $_POST['fullname'] = trim(str_replace(':','',$_POST['fullname']));
  $_POST['email']    = trim(str_replace(':','',$_POST['email']));

  if( empty($_POST['login']) ||
      empty($_POST['fullname']) ||
      empty($_POST['email']) ){
    msg($lang['regmissing'],-1);
    return false;
  }

  //check mail
  if(!mail_isvalid($_POST['email'])){
    msg($lang['regbadmail'],-1);
    return false;
  }

  //okay try to create the user
  $pass = auth_createUser($_POST['login'],$_POST['fullname'],$_POST['email']);
  if(empty($pass)){
    msg($lang['reguexists'],-1);
    return false;
  }

  //send him the password
  if (auth_sendPassword($_POST['login'],$pass)){
    msg($lang['regsuccess'],1);
    return true;
  }else{
    msg($lang['regmailfail'],-1);
    return false;
  }
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



//Setup VIM: ex: et ts=2 enc=utf-8 :
