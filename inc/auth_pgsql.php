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

  $dsn="host=".$cnf['server']." port=5432 dbname=".$cnf['database']." user=".$cnf['user']." password=".$cnf['password'];
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

  $sql    = str_replace('%u',addslashes($user),$cnf['passcheck']);
  $sql    = str_replace('%p',addslashes($pass),$sql);
  $result = auth_pgsql_runsql($sql);
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
 *
 * Not implemented
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function auth_createUser($user,$name,$mail){
  msg("Sorry. Creating users is not supported by the PgSQL backend, yet",-1);
  return null;
}



//Setup VIM: ex: et ts=2 enc=utf-8 :
