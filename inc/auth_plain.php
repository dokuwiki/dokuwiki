<?php

/**
 * If you want to authenticate against something
 * else then the builtin flatfile auth system
 * you have to reimplement the "required auth
 * functions"
 */


/**
 * required auth function
 *
 * Checks if the given user exists and the given
 * plaintext password is correct
 */
function auth_checkPass($user,$pass){
  $users = auth_loadUserData();
  $pass = md5($pass); //encode pass

  if($users[$user]['pass'] == $pass){
    return true;
  }else{
    return false;
  }
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
 */
function auth_getUserData($user){
  $users = auth_loadUserData();
  return $users[$user];
}

/**
 * Required auth function
 *
 * Creates a new user.
 *
 * Returns false if the user already exists, null when an error
 * occured and the cleartext password of the new user if
 * everything went well.
 * 
 * The new user HAS TO be added to the default group by this
 * function!
 */
function auth_createUser($user,$name,$mail){
  global $conf;

  $users = auth_loadUserData();
  if(isset($users[$user])) return false;

  $pass = auth_pwgen();
  $userline = join(':',array($user,
                             md5($pass),
                             $name,
                             $mail,
                             $conf['defaultgroup']));
  $userline .= "\n";
  $fh = fopen('conf/users.auth','a');
  if($fh){
    fwrite($fh,$userline);
    fclose($fh);
    return $pass;
  }
  msg('The users.auth file is not writable. Please inform the Wiki-Admin',-1);
  return null;
}

/**
 * used by the plaintext auth functions
 * loads the user file into a datastructure
 */
function auth_loadUserData(){
  $data = array();
  $lines = file('conf/users.auth');
  foreach($lines as $line){
    $line = preg_replace('/#.*$/','',$line); //ignore comments
    $line = trim($line);
    if(empty($line)) continue;

    $row    = split(":",$line,5);
    $groups = split(",",$row[4]);
    $data[$row[0]]['pass'] = $row[1];
    $data[$row[0]]['name'] = urldecode($row[2]);
    $data[$row[0]]['mail'] = $row[3];
    $data[$row[0]]['grps'] = $groups;
  }
  return $data;
}

?>
