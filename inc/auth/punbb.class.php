<?php
/**
 * PunBB auth backend
 *
 * Uses external Trust mechanism to check against PunBB's
 * user cookie. PunBB's PUN_ROOT must be defined correctly.
 *
 * It inherits from the MySQL module, so you may set up
 * the correct SQL strings for user modification if you like.
 *
 * @todo      This is far from perfect yet. SQL Strings should be
 *            predefined. Logging in should be handled correctly.
 * @author    Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('PUN_ROOT')) define('PUN_ROOT', DOKU_INC.'../forum/');
require_once PUN_ROOT.'include/common.php';
require_once DOKU_INC.'inc/auth/mysql.class.php';

class auth_punbb extends auth_mysql {

  /**
   * Just checks against the $pun_user variable
   */
  function trustExternal($user,$pass,$sticky=false){
    global $USERINFO;
    global $conf;
    global $pun_user;
    $sticky ? $sticky = true : $sticky = false; //sanity check

    // someone used the login form
    if(isset($user)){
      msg('Please login at the forum',-1);
      //FIXME a redirect to PunBBs login would be nice here
      auth_logoff();
      return false;
    }

    if(isset($pun_user) && !$pun_user['is_guest']){
      // okay we're logged in - set the globals
      $USERINFO['name'] = $pun_user['username'];
      $USERINFO['mail'] = $pun_user['email'];
      $USERINFO['grps'] = array($pun_user['g_title']);

      $_SERVER['REMOTE_USER'] = $pun_user['username'];
      $_SESSION[$conf['title']]['auth']['user'] = $pun_user['username'];
      $_SESSION[$conf['title']]['auth']['info'] = $USERINFO;
      return true;
    }

    // to be sure
    auth_logoff();
    return false;
  }
}
