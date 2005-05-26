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

//check for LDAP extension on load
if(!function_exists('ldap_connect'))
  msg("LDAP extension not found",-1);

/**
 * Connect to the LDAP server
 *
 * Holds the connection in global scope for multiple use
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
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
    //set protocol version
    if($cnf['version']){
      if(!@ldap_set_option($LDAP_CONNECTION,
                           LDAP_OPT_PROTOCOL_VERSION,
                           $cnf['version'])){
        msg('Setting LDAP Protocol version '.$cnf['version'].' failed',-1);
        if($cnf['debug'])
          msg('LDAP errstr: '.htmlspecialchars(ldap_error($LDAP_CONNECTION)),0);

      } else {
        //use TLS (needs version 3)
        if($cnf['starttls']) {
          if (!@ldap_start_tls($LDAP_CONNECTION)){
            msg('Starting TLS failed',-1);
            if($cnf['debug'])
              msg('LDAP errstr: '.htmlspecialchars(ldap_error($LDAP_CONNECTION)),0);
          }
        }
        // needs version 3
        if(isset($cnf['referrals'])) {
          if(!@ldap_set_option($LDAP_CONNECTION,
                           LDAP_OPT_REFERRALS,
                           $cnf['referrals'])){
            msg('Setting LDAP referrals to off failed',-1);
            if($cnf['debug'])
              msg('LDAP errstr: '.htmlspecialchars(ldap_error($LDAP_CONNECTION)),0);
          }
        }
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

  //reject empty password
  if(empty($pass)) return false;

  //connect to LDAP Server
  $conn = auth_ldap_connect();
  if(!$conn) return false;

  if(!empty($cnf['userfilter'])) {
    //get dn for given user
    $info = auth_getUserData($user);
    $dn   = $info['dn'];  
    if(!$dn) return false;
  } else {
    // dn is defined in the usertree
    $dn = auth_ldap_makeFilter($cnf['usertree'], array('user'=>$user)); 
  }
  //try to bind with dn
  if(@ldap_bind($conn,$dn,$pass)){
    if($cnf['debug']) msg('LDAP errstr: '.htmlspecialchars(ldap_error($conn)),0);
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
 * @author  Dan Allen <dan.j.allen@gmail.com>
 * @auhtor  <evaldas.auryla@pheur.org>
 */
function auth_getUserData($user){
  global $conf;
  $cnf = $conf['auth']['ldap'];

  //connect to LDAP Server
  $conn = auth_ldap_connect();
  if(!$conn) return false;

  //bind to server to lookup userdata
  if ($cnf['binddn']) {
    //use superuser credentials
    if(!@ldap_bind($conn,$cnf['binddn'],$cnf['bindpw'])){
      msg("LDAP: can not bind as superuser",-1);
      if($cnf['debug']) msg('LDAP errstr: '.htmlspecialchars(ldap_error($conn)),0);
      return false;
    }
  }elseif(!empty($cnf['userfilter'])){
    //bind anonymous if we need to do a search for the dn
    if(!@ldap_bind($conn)){
      msg("LDAP: can not bind anonymously",-1);
      if($cnf['debug']) msg('LDAP errstr: '.htmlspecialchars(ldap_error($conn)),0);
      return false;
    }
  }
  $info['user']= $user;

  //get info for given user
  $base = auth_ldap_makeFilter($cnf['usertree'], $info); 
  if(!empty($cnf['userfilter'])) {
    $filter = auth_ldap_makeFilter($cnf['userfilter'], $info); 
  } else {
    $filter = "(ObjectClass=*)";
  }
  $sr     = ldap_search($conn, $base, $filter);;
  $result = ldap_get_entries($conn, $sr);
  $user_result = $result[0]; 
  if($result['count'] != 1){
    return false; //user not found
  }

  //general user info
  $info['dn']= $user_result['dn'];
  $info['mail']= $user_result['mail'][0];
  $info['name']= $user_result['cn'][0];

  //handle ActiveDirectory memberOf
  if(is_array($result[0]['memberof'])){
    foreach($result[0]['memberof'] as $grp){
      if (preg_match("/CN=(.+?),/i",$grp,$match)) {
        $info['grps'][] = trim($match[1]);
      }
    }
  }

  //get groups for given user if grouptree is given
  if (!empty($cnf['grouptree'])) {
    $base = auth_ldap_makeFilter($cnf['grouptree'], $user_result); 
    $filter = auth_ldap_makeFilter($cnf['groupfilter'], $user_result); 

    $sr = @ldap_search($conn, $base, $filter);
    if(!$sr){
      msg("LDAP: Reading group memberships failed",-1);
      if($cnf['debug']) msg('LDAP errstr: '.htmlspecialchars(ldap_error($conn)),0);
      return false;
    }
    $result = ldap_get_entries($conn, $sr);
    foreach($result as $grp){
      if(!empty($grp['cn'][0]))
        $info['grps'][] = $grp['cn'][0];
    }
  }

  //if no groups were found always return the default group
  if(!count($info['grps'])) $info['grps'][] = $conf['defaultgroup'];
  
  return $info;
}

/**
 * Create a new User [required auth function]
 *
 * Not implemented
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_createUser($user,$pass,$name,$mail){
  msg("Sorry. Creating users is not supported by the LDAP backend",-1);
  return null;
}


/**
 * Make ldap filter strings.
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
function auth_ldap_makeFilter($filter, $placeholders) {
  preg_match_all("/%{([^}]+)/", $filter, $matches, PREG_PATTERN_ORDER);
  //replace each match
  foreach ($matches[1] as $match) {
    //take first element if array
    if(is_array($placeholders[$match])) {
      $value = $placeholders[$match][0];
    } else {
      $value = $placeholders[$match];
    }
    $filter = str_replace('%{'.$match.'}', $value, $filter);
  }
  return $filter;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
