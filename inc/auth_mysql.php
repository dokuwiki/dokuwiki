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
  }
  if (mysql_insert_id($link)) {
    $resultarray = mysql_insert_id($link); //give back ID on insert
  }
  mysql_close ($link);
  return $resultarray;
}

/**
 * Check user+password [required auth function]
 *
 * Checks if the given user exists and the given
 * plaintext password is correct
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @return  bool
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
 * Not implemented
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_createUser($user,$name,$mail){
  msg("Sorry. Creating users is not supported by the MySQL backend, yet",-1);
  return null;
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
