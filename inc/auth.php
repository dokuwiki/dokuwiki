<?
require_once("inc/common.php");
require_once("inc/io.php");
# load the the auth functions
require_once('inc/auth_'.$conf['authtype'].'.php');

# some ACL level defines
define('AUTH_NONE',0);
define('AUTH_READ',1);
define('AUTH_EDIT',2);
define('AUTH_CREATE',4);
define('AUTH_UPLOAD',8);
define('AUTH_GRANT',255);

if($conf['useacl']){
  auth_login($_REQUEST['u'],$_REQUEST['p']);
  # load ACL into a global array
  $AUTH_ACL = file('conf/acl.auth');
}

/**
 * This tries to login the user based on the sent auth credentials
 *
 * The authentication works like this: if a username was given
 * a new login is assumed and user/password are checked - if they
 * are correct a random authtoken is created which is stored in
 * the session _and_ in a cookie.
 * The user stays logged in as long as the session and the cookie
 * match. This still isn't the securest method but requires an
 * attacker to steal an existing session _and_ the authtoken
 * cookie. The actual password is only transfered once per login.
 * 
 * On a successful login $_SERVER[REMOTE_USER] and $USERINFO
 * are set.
*/
function auth_login($user,$pass){
  global $USERINFO;
  global $conf;
  global $lang;
  $cookie  = $_COOKIE['AUTHTOKEN'];
	$session = $_SESSION[$conf['title']]['authtoken'];

  if(isset($user)){
    if (auth_checkPass($user,$pass)){
      //make username available as REMOTE_USER
      $_SERVER['REMOTE_USER'] = $user;
      //set global user info
      $USERINFO = auth_getUserData($user);
      //set authtoken
      $token = md5(uniqid(rand(), true));
      $_SESSION[$conf['title']]['user']      = $user;
      $_SESSION[$conf['title']]['authtoken'] = $token;
      setcookie('AUTHTOKEN', $token);
    }else{
      //invalid credentials - log off
      msg($lang['badlogin'],-1);
      auth_logoff();
    }
  }elseif(isset($cookie) && isset($session)){
    if($cookie == $session){
      //make username available as REMOTE_USER
      $_SERVER['REMOTE_USER'] = $_SESSION[$conf['title']]['user'];
      //set global user info
      $USERINFO = auth_getUserData($_SERVER['REMOTE_USER']);
    }else{
      //bad token
      auth_logoff();
    }
  }else{
    //just to be sure
    auth_logoff();
  }
}

/**
 * This clears all authenticationdata and thus log the user
 * off
 */
function auth_logoff(){
  global $conf;
  global $USERINFO;
  unset($_SESSION[$conf['title']]['authtoken']);
  unset($_SESSION[$conf['title']]['user']);
  unset($_SERVER['REMOTE_USER']);
  $USERINFO=null;
}

/**
 * Convinience function for auth_aclcheck
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
 */
function auth_aclcheck($id,$user,$groups){
  global $conf;
  global $AUTH_ACL;

  # if no ACL is used always return upload rights
  if(!$conf['useacl']) return AUTH_UPLOAD;

  $ns    = getNS($id);
  $perm  = -1;

  if($user){
    //prepend groups with @
    for($i=0; $i<count($groups); $i++){
      $groups[$i] = '@'.$groups[$i];
    }
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
}

/**
 * Create a pronouncable password
 *
 * @see: http://www.phpbuilder.com/annotate/message.php3?id=1014451
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
 * returns true on success
 */
function auth_sendPassword($user,$password){
  global $conf;
  global $lang;
  $users = auth_loadUserData();
  $hdrs  = '';

  if(!$users[$user]['mail']) return false;

  $text = rawLocale('password');
  $text = str_replace('@DOKUWIKIURL@',getBaseURL(true),$text);
  $text = str_replace('@FULLNAME@',$users[$user]['name'],$text);
  $text = str_replace('@LOGIN@',$user,$text);
  $text = str_replace('@PASSWORD@',$password,$text);
  $text = str_replace('@TITLE@',$conf['title'],$text);

  if (!empty($conf['mailfrom'])) {
    $hdrs = 'From: '.$conf['mailfrom']."\n";
  }
  return @mail($users[$user]['mail'],$lang['regpwmail'],$text,$hdrs);
}

/**
 * The new user registration - we get our info directly from
 * $_POST
 *
 * It returns true on success and false on any error
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
  if(!isvalidemail($_POST['email'])){
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
 * @see http://www.webmasterworld.com/forum88/135.htm
 *
 * May not be completly RFC conform!
 */
function isvalidemail($email){
  return eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$", $email);
}

?>
