<?php
/**
 * PgSQL authentication backend 
 *   (shamelessly based on the original auth_mysql.php ;-)
 *
 * PHP's PgSQL extension is needed
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Alexander Marx < mad-ml [at] madness [dot] at >
 */

//check for Postgresql extension on load
if(!function_exists('pg_connect'))
  msg("PgSQL extension not found",-1);

/**
 * Execute SQL
 *
 * Executes SQL statements and returns the results as list
 * of hashes. Returns false on error.
 *
 */
function auth_pgsql_runsql($sql_string) {
  global $conf;
  $cnf = $conf['auth']['pgsql'];

  if($cnf['port']) {
      $port=" port=".$cnf['port'];
  }

  $dsn="host=".$cnf['server']." dbname=".$cnf['database'].$port." user=".$cnf['user']." password=".$cnf['password'];
  $link   = pg_connect($dsn);
  if(!$link){
    msg('PgSQL: Connection to database failed!',-1);
    return false;
  }
  
  $result = pg_query($link, $sql_string);
  if(!$result){
    msg('PgSQL: '.pg_last_error($link));
    return false;
  }
  
  for($i=0; $i< pg_num_rows($result); $i++) {
    $temparray = pg_fetch_assoc($result);
    $resultarray[]=$temparray;
  }
  pg_free_result($result);
  pg_close($link);
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
  $cnf = $conf['auth']['pgsql'];

  $sql    = str_replace('%u',addslashes($user),$cnf['userinfo']);
  $result = auth_pgsql_runsql($sql);
  if(count($result)>0) {
    $info=$result[0];
    return auth_verifyPassword($pass, $info['pass']);
  } else {
    return false;
  }
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
  $cnf = $conf['auth']['pgsql'];

  $sql    = str_replace('%u',addslashes($user),$cnf['userinfo']);
  $result = auth_pgsql_runsql($sql);
  if(!count($result)) return false;
  $info   = $result[0];

  $sql    = str_replace('%u',addslashes($user),$cnf['groups']);
  $result = auth_pgsql_runsql($sql);
  if(!count($result)) return false;
  foreach($result as $row){
    $info['grps'][] = $row['group'];
  }

  return $info;
}

/**
 * Create a new User [required auth function]
 */
function auth_createUser($user,$pass,$name,$mail) {
  global $conf;
  $cnf = $conf['auth']['pgsql'];

  if($cnf['createuser']) {
    $sql    = str_replace('%u',addslashes($user),$cnf['userinfo']);
    $result = auth_pgsql_runsql($sql);
    if(count($result)>0) return false;

      $sql    = str_replace('%u',addslashes($user),$cnf['createuser']);
      $sql    = str_replace('%p',auth_cryptPassword($pass),$sql);
      $sql    = str_replace('%f',addslashes($name),$sql);
      $sql    = str_replace('%e',addslashes($mail),$sql);
      $sql    = str_replace('%g',addslashes($conf['defaultgroup']),$sql);

      $result=auth_pgsql_runsql($sql);
      if(count($result))
        return $pass;
  } else {
    msg("Sorry. Your PgSQL backend is not configured to create new users.",-1);
  }
  return null;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :

