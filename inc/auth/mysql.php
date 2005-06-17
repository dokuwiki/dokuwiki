<?php
/**
 * MySQL authentication backend
 *
 * PHP's MySQL extension is needed
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//check for MySQL extension on load
if(!function_exists('mysql_connect'))
  msg("MySQL extension not found",-1);

/**
 * Execute SQL
 *
 * Executes SQL statements and returns the results as list
 * of hashes. Returns false on error. Returns auto_increment
 * IDs on INSERT statements.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_mysql_runsql($sql_string) {
  global $conf;
  $cnf = $conf['auth']['mysql'];

  $link   = @mysql_connect ($cnf['server'], $cnf['user'], $cnf['password']);
  if(!$link){
    msg('MySQL: Connection to database failed!',-1);
    return false;
  }
  $result = @mysql_db_query($cnf['database'],$sql_string,$link);
  if(!$result){
    msg('MySQL: '.mysql_error($link));
    return false;
  }
  
  //mysql_db_query returns 1 on a insert statement -> no need to ask for results
  if ($result != 1) {
    for($i=0; $i< mysql_num_rows($result); $i++) {
      $temparray = mysql_fetch_assoc($result);
      $resultarray[]=$temparray;
    }
    mysql_free_result ($result);
  } elseif (mysql_insert_id($link)) {
    $resultarray = mysql_insert_id($link); //give back ID on insert
  } else
    $resultarray = 0; // asure that the return value is valid
    
  mysql_close ($link);
  return $resultarray;
}

/**
 * Check user+password [required auth function]
 *
 * Checks if the given user exists and the given plaintext password
 * is correct. Furtheron it might be checked wether the user is
 * member of the right group
 *
 * Depending on which SQL string is defined in the config, password
 * checking is done here (getpass) or by the database (passcheck)
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @return  bool
 */
function auth_checkPass($user,$pass){
  global $conf;
  $cnf = $conf['auth']['mysql'];

  if($cnf['getpass']){
    // we check the pass ourself against the crypted one
    $sql    = str_replace('%u',addslashes($user),$cnf['getpass']);
    $sql    = str_replace('%g',addslashes($conf['defaultgroup']),$sql);
    $result = auth_mysql_runsql($sql);
  
    if(count($result)){
      return(auth_verifyPassword($pass,$result[0]['pass']));
    }
  }else{
    // we leave pass checking to the database
    $sql    = str_replace('%u',addslashes($user),$cnf['passcheck']);
    $sql    = str_replace('%g',addslashes($conf['defaultgroup']),$sql);
    $sql    = str_replace('%p',addslashes($pass),$sql);
    $result = auth_mysql_runsql($sql);

    if(count($result) == 1){
      return true;
    }
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
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_getUserData($user){
  global $conf;
  $cnf = $conf['auth']['mysql'];

  $sql    = str_replace('%u',addslashes($user),$cnf['userinfo']);
  $result = auth_mysql_runsql($sql);
  if(!count($result)) return false;
  $info   = $result[0];

  $sql    = str_replace('%u',addslashes($user),$cnf['groups']);
  $result = auth_mysql_runsql($sql);
  if(!count($result)){
    $info['grps'][] = $conf['defaultgroup'];
  }else{
    foreach($result as $row){
      $info['grps'][] = $row['group'];
    }
  }

  return $info;
}

/**
 * Create a new User [required auth function]
 *
 * user string  username
 * pass string  password
 * name string  full name of the user
 * mail string  email address
 *
 * Returns false if the user already exists, null when an error
 * occoured and the cleartext password of the new user if
 * everything went well.
 *
 * The user HAS TO be added to the default group by this
 * function
 *
 * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
function auth_createUser($user,$pass,$name,$mail){
  global $conf;
  $cnf = $conf['auth']['mysql'];
  
  //check if user exists
  $info = auth_getUserData($user);
  if ($info != false) return false;
 
  //get groupid of default group
  if($cnf['getgroupid']){
    $sql    = str_replace('%g',addslashes($conf['defaultgroup']),$cnf['getgroupid']);
    $result = auth_mysql_runsql($sql);
    if($result === false) return null;
    if (count($result) == 1){
      $gid = $result[0]['gid'];
    }else{
      msg("MySQL: Couldn't find the default group",-1);
      return null;
    }
  }
  
  //prepare the insert 
  $sql = str_replace('%u'  ,addslashes($user),$cnf['adduser']);
  $sql = str_replace('%p'  ,addslashes(auth_cryptPassword($pass)),$sql);
  $sql = str_replace('%n'  ,addslashes($name),$sql);
  $sql = str_replace('%e'  ,addslashes($mail),$sql);
  $sql = str_replace('%gid',addslashes($gid),$sql);
  $sql = str_replace('%g'  ,addslashes($conf['defaultgroup']),$sql);

  //do the insert
  $uid  = auth_mysql_runsql($sql);
  if($uid == 0){
    msg("Registering of the new user '$user' failed!", -1);
    return null;
  }

  //add to default group  
  if ($cnf['addusergroup']) {
    $sql = str_replace('%uid',addslashes($uid),$cnf['addusergroup']);
    $sql = str_replace('%u'  ,addslashes($user),$sql);
    $sql = str_replace('%gid',addslashes($gid),$sql);
    $sql = str_replace('%g'  ,addslashes($conf['defaultgroup']),$sql);
    $result = auth_mysql_runsql($sql);
    if($result === false) msg("MySQL: couldn't add user to the default group");
  }

  return $pass;
}
    
//Setup VIM: ex: et ts=2 enc=utf-8 :
