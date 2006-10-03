<?php
/**
 * PunBB auth backend
 *
 * Uses external Trust mechanism to check against PunBB's
 * user cookie. PunBB's PUN_ROOT must be defined correctly.
 *
 * @author    Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('PUN_ROOT')) define('PUN_ROOT', DOKU_INC.'../forum/');
if(get_magic_quotes_gpc()){
  nice_die('Sorry the punbb auth backend requires the PHP option
  <a href="http://www.php.net/manual/en/ref.info.php#ini.magic-quotes-gpc">magic_quotes_gpc</a>
  to be disabled for proper operation. Either setup your PHP install accordingly or
  choose a different auth backend.');
}

require_once PUN_ROOT.'include/common.php';
require_once DOKU_INC.'inc/auth/mysql.class.php';

#dbg($GLOBALS);
#dbg($pun_user);

class auth_punbb extends auth_mysql {

  /**
   * Constructor.
   *
   * Sets additional capabilities and config strings
   */
  function auth_punbb(){
    global $conf;
    $this->cando['external'] = true;
    $this->cando['logoff']   = true;

    // make sure we use a crypt understood by punbb
    if(function_exists('sha1')){
      $conf['passcrypt'] = 'sha1';
    }else{
      $conf['passcrypt'] = 'md5';
    }

    // get global vars from PunBB config
    global $db_host;
    global $db_name;
    global $db_username;
    global $db_password;
    global $db_prefix;

    // now set up the mysql config strings
    $conf['auth']['mysql']['server']   = $db_host;
    $conf['auth']['mysql']['user']     = $db_username;
    $conf['auth']['mysql']['password'] = $db_password;
    $conf['auth']['mysql']['database'] = $db_name;

    $conf['auth']['mysql']['checkPass']   = "SELECT u.password AS pass
                                               FROM ${db_prefix}users AS u, ${db_prefix}groups AS g
                                              WHERE u.group_id = g.g_id
                                                AND u.username = '%{user}'
                                                AND g.g_title   != 'Guest'";
    $conf['auth']['mysql']['getUserInfo'] = "SELECT password AS pass, realname AS name, email AS mail,
                                                    id, g_title as `group`
                                               FROM ${db_prefix}users AS u, ${db_prefix}groups AS g
                                              WHERE u.group_id = g.g_id
                                                AND u.username = '%{user}'";
    $conf['auth']['mysql']['getGroups']   = "SELECT g.g_title as `group`
                                               FROM ${db_prefix}users AS u, ${db_prefix}groups AS g
                                              WHERE u.group_id = g.g_id
                                                AND u.username = '%{user}'";
    $conf['auth']['mysql']['getUsers']    = "SELECT DISTINCT u.username AS user
                                               FROM ${db_prefix}users AS u, ${db_prefix}groups AS g
                                              WHERE u.group_id = g.g_id";
    $conf['auth']['mysql']['FilterLogin'] = "u.username LIKE '%{user}'";
    $conf['auth']['mysql']['FilterName']  = "u.realname LIKE '%{name}'";
    $conf['auth']['mysql']['FilterEmail'] = "u.email    LIKE '%{email}'";
    $conf['auth']['mysql']['FilterGroup'] = "g.g_title    LIKE '%{group}'";
    $conf['auth']['mysql']['SortOrder']   = "ORDER BY u.username";
    $conf['auth']['mysql']['addUser']     = "INSERT INTO ${db_prefix}users
                                                    (username, password, email, realname)
                                             VALUES ('%{user}', '%{pass}', '%{email}', '%{name}')";
    $conf['auth']['mysql']['addGroup']    = "INSERT INTO ${db_prefix}groups (g_title) VALUES ('%{group}')";
    $conf['auth']['mysql']['addUserGroup']= "UPDATE ${db_prefix}users
                                                SET group_id=%{gid}
                                              WHERE id='%{uid}'";
    $conf['auth']['mysql']['delGroup']    = "DELETE FROM ${db_prefix}groups WHERE g_id='%{gid}'";
    $conf['auth']['mysql']['getUserID']   = "SELECT id FROM ${db_prefix}users WHERE username='%{user}'";
    $conf['auth']['mysql']['updateUser']  = "UPDATE ${db_prefix}users SET";
    $conf['auth']['mysql']['UpdateLogin'] = "username='%{user}'";
    $conf['auth']['mysql']['UpdatePass']  = "password='%{pass}'";
    $conf['auth']['mysql']['UpdateEmail'] = "email='%{email}'";
    $conf['auth']['mysql']['UpdateName']  = "realname='%{name}'";
    $conf['auth']['mysql']['UpdateTarget']= "WHERE id=%{uid}";
    $conf['auth']['mysql']['delUserGroup']= "UPDATE ${db_prefix}users SET g_id=4 WHERE id=%{uid}";
    $conf['auth']['mysql']['getGroupID']  = "SELECT g_id AS id FROM ${db_prefix}groups WHERE g_title='%{group}'";

    $conf['auth']['mysql']['TablesToLock']= array("${db_prefix}users", "${db_prefix}users AS u",
                                                  "${db_prefix}groups", "${db_prefix}groups AS g");

    $conf['auth']['mysql']['debug'] = 1;
    // call mysql constructor
    $this->auth_mysql();
  }

  /**
   * Just checks against the $pun_user variable
   */
  function trustExternal($user,$pass,$sticky=false){
    global $USERINFO;
    global $conf;
    global $lang;
    global $pun_user;
    global $pun_config;
    $sticky ? $sticky = true : $sticky = false; //sanity check

    // someone used the login form
    if(!empty($user)){
      if($this->checkPass($user,$pass)){
        $expire = ($sticky) ? time() + 31536000 : 0;
        $uinfo  = $this->getUserData($user);
        pun_setcookie($uinfo['id'], auth_cryptPassword($pass), $expire);
        $pun_user = array();
        $pun_user['password'] = auth_cryptPassword($pass);
        $pun_user['username'] = $user;
        $pun_user['realname'] = $uinfo['name'];
        $pun_user['email']    = $uinfo['mail'];
        $pun_user['g_title']  = $uinfo['group'];
      }else{
        //invalid credentials - log off
        msg($lang['badlogin'],-1);
        auth_logoff();
        return false;
      }
    }

    if(isset($pun_user) && !$pun_user['is_guest']){
      // okay we're logged in - set the globals
      $USERINFO['pass'] = $pun_user['password'];
      $USERINFO['name'] = $pun_user['realname'];
      $USERINFO['mail'] = $pun_user['email'];
      $USERINFO['grps'] = array($pun_user['g_title']);

      $_SERVER['REMOTE_USER'] = $pun_user['username'];
      $_SESSION[DOKU_COOKIE]['auth']['user'] = $pun_user['username'];
      $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
      return true;
    }

    // to be sure
    auth_logoff();
    return false;
  }

  /**
   * remove punbb cookie on logout
   */
  function logOff(){
    global $pun_user;
    $pun_user = array();
    $pun_user['is_guest'] = 1;
    pun_setcookie(1, random_pass(8), time() + 31536000);
  }
}
//Setup VIM: ex: et ts=2 enc=utf-8 :
