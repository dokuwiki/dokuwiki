<?php
/**
 * LDAP authentication backend
 * 
 * tested with openldap 2.x on Debian only
 *
 * PHPs LDAP extension is needed
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Connect to the LDAP server
 *
 * Holds the connection in global scope for multiple use
 *
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
function auth_ldap_connect(){
  global $LDAP_CONNECTION;
  global $conf;
  $cnf = $conf['auth']['ldap'];

  if(!$LDAP_CONNECTION){
    $LDAP_CONNECTION = @ldap_connect($cnf['server']);
    if(!$LDAP_CONNECTION){
      msg("LDAP: couldn't connect to LDAP server",-1);
      return false;
    }
    if($cnf['version']){
      if(!@ldap_set_option($LDAP_CONNECTION,
                           LDAP_OPT_PROTOCOL_VERSION,
                           $cnf['version'])){
        msg('Setting LDAP Protocol version '.$cnf['version'].' failed',-1);
      }
    }
  }
  return $LDAP_CONNECTION;
}

/**
 * Check user+password [required auth function]
 *
 * Checks if the given user exists and the given
 * plaintext password is correct by trying to bind
 * to the LDAP server
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return  bool
 */
function auth_checkPass($user,$pass){
  global $conf;
  $cnf = $conf['auth']['ldap'];

  //connect to LDAP Server
  $conn = auth_ldap_connect();
  if(!$conn) return false;

  //get dn for given user
  $info = auth_getUserData($user);
  $dn   = $info['dn'];
  if(!$dn) return false;

  //try to bind with dn
  if(@ldap_bind($conn,$dn,$pass)){
    return true;
  }
  return false;
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
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Trouble
 */
function auth_getUserData($user){
  global $conf;
  $cnf = $conf['auth']['ldap'];

  //connect to LDAP Server
  $conn = auth_ldap_connect();
  if(!$conn) return false;

  //anonymous bind to lookup userdata
  if(!@ldap_bind($conn)){
    msg("LDAP: can not bind anonymously",-1);
    return false;
  }

  //get info for given user
  $filter = str_replace('%u',$user,$cnf['userfilter']);
  $base   = str_replace('%u',$user,$cnf['usertree']);
  $sr     = ldap_search($conn, $base, $filter);;
  $result = ldap_get_entries($conn, $sr);
  if($result['count'] != 1){
    return false; //user not found
  }

  //general user info
  $info['dn']  = $result[0]['dn'];
  $info['mail']= $result[0]['mail'][0];
  $info['name']= $result[0]['cn'][0];
  $info['uid'] = $result[0]['uid'][0];
  
  //primary group id
  $gid = $result[0]['gidnumber'][0];

  //get groups for given user if grouptree is given
  if ($cnf['grouptree'] != '') {
    $filter = "(&(objectClass=posixGroup)(|(gidNumber=$gid)(memberUID=".$info['uid'].")))";
    $sr     = @ldap_search($conn, $cnf['grouptree'], $filter);
    if(!$sr){
      msg("LDAP: Reading group memberships failed",-1);
      return false;
    }
    $result = ldap_get_entries($conn, $sr);
    foreach($result as $grp){
      if(!empty($grp['cn'][0]))
        $info['grps'][] = $grp['cn'][0];
    }
  }else{
    //if no groups are available in LDAP always return the default group
    $info['grps'][] = $conf['defaultgroup'];
  }
  return $info;
}

/**
 * Create a new User [required auth function]
 *
 * Not implemented
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_createUser($user,$name,$mail){
  msg("Sorry. Creating users is not supported by the LDAP backend",-1);
  return null;
}

?>
