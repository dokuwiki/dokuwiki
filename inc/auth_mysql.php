<?php
/**
 * This is used to authenticate against an MySQL server
 *
 * PHPs MySQL extension is needed
 */

/**
 * Executes SQL statements and returns the results as list
 * of hashes. Returns false on error. Returns auto_increment
 * IDs on INSERT statements.
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
  }
  if (mysql_insert_id($link)) {
    $resultarray = mysql_insert_id($link); //give back ID on insert
  }
  mysql_close ($link);
  return $resultarray;
}

/**
 * required auth function
 *
 * Checks if a user with the given password exists
 */
function auth_checkPass($user,$pass){
  global $conf;
  $cnf = $conf['auth']['mysql'];

  $sql    = str_replace('%u',addslashes($user),$cnf['passcheck']);
  $sql    = str_replace('%p',addslashes($pass),$sql);
  $result = auth_mysql_runsql($sql);
  return(count($result));
}

/**
 * Required auth function
 *
 * Returns info about the given user needs to contain
 * at least these fields:
 *
 * name string  full name of the user
 * mail string  email addres of the user
 * grps array   list of groups the user is in
 *
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
  if(!count($result)) return false;
  foreach($result as $row){
    $info['grps'][] = $row['group'];
  }

  return $info;
}

/**
 * Required auth function
 *
 * Not implemented
 */
function auth_createUser($user,$name,$mail){
  msg("Sorry. Creating users is not supported by the MySQL backend, yet",-1);
  return null;
}

?>
