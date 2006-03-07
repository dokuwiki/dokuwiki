<?php
/**
 * DokuWiki Actions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/template.php');


/**
 * Call the needed action handlers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_dispatch(){
  global $INFO;
  global $ACT;
  global $ID;
  global $QUERY;
  global $lang;
  global $conf;

  //sanitize $ACT
  $ACT = act_clean($ACT);

  //check if searchword was given - else just show
  $s = cleanID($QUERY);
  if($ACT == 'search' && empty($s)){
    $ACT = 'show';
  }

  //login stuff
  if(in_array($ACT,array('login','logout')))
    $ACT = act_auth($ACT);

  //check if user is asking to (un)subscribe a page
  if($ACT == 'subscribe' || $ACT == 'unsubscribe')
    $ACT = act_subscription($ACT);

  //check permissions
  $ACT = act_permcheck($ACT);

  //register
  if($ACT == 'register' && register()){
    $ACT = 'login';
  }

  if ($ACT == 'resendpwd' && act_resendpwd()) {
    $ACT = 'login';
  }

  //update user profile
  if (($ACT == 'profile') && updateprofile()) {
    msg($lang['profchanged'],1);
    $ACT = 'show';
  }

  //save
  if($ACT == 'save')
    $ACT = act_save($ACT);

  //edit
  if(($ACT == 'edit' || $ACT == 'preview') && $INFO['editable']){
    $ACT = act_edit($ACT);
  }else{
    unlock($ID); //try to unlock
  }

  //handle export
  if(substr($ACT,0,7) == 'export_')
    $ACT = act_export($ACT);

  //display some infos
  if($ACT == 'check'){
    check();
    $ACT = 'show';
  }

  //handle admin tasks
  if($ACT == 'admin'){
    // retrieve admin plugin name from $_REQUEST['page']
    if ($_REQUEST['page']) {
        $pluginlist = plugin_list('admin');
        if (in_array($_REQUEST['page'], $pluginlist)) {
          // attempt to load the plugin
          if ($plugin =& plugin_load('admin',$_REQUEST['page']) !== NULL)
              $plugin->handle();
        }
    }
/*
        if($_REQUEST['page'] == 'acl'){
            require_once(DOKU_INC.'inc/admin_acl.php');
            admin_acl_handler();
    }
*/
  }

  //call template FIXME: all needed vars available?
  header('Content-Type: text/html; charset=utf-8');
  include(template('main.php'));
  // output for the commands is now handled in inc/templates.php
  // in function tpl_content()
}

/**
 * Sanitize the action command
 *
 * Add all allowed commands here.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_clean($act){
  global $lang;
  global $conf;

  //handle localized buttons
  if($act == $lang['btn_save']) $act = 'save';
  if($act == $lang['btn_preview']) $act = 'preview';
  if($act == $lang['btn_cancel']) $act = 'show';

  //remove all bad chars
  $act = strtolower($act);
  $act = preg_replace('/[^a-z_]+/','',$act);

  if($act == 'export_html') $act = 'export_xhtml';
  if($act == 'export_htmlbody') $act = 'export_xhtmlbody';

  //disable all acl related commands if ACL is disabled
  if(!$conf['useacl'] && in_array($act,array('login','logout','register','admin',
                                             'subscribe','unsubscribe','profile',
                                             'resendpwd',))){
    msg('Command unavailable: '.htmlspecialchars($act),-1);
    return 'show';
  }

  if(array_search($act,array('login','logout','register','save','edit',
                             'preview','search','show','check','index','revisions',
                             'diff','recent','backlink','admin','subscribe',
                             'unsubscribe','profile','resendpwd',)) === false
     && substr($act,0,7) != 'export_' ) {
    msg('Unknown command: '.htmlspecialchars($act),-1);
    return 'show';
  }
  return $act;
}

/**
 * Run permissionchecks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_permcheck($act){
  global $INFO;
  global $conf;

  if(in_array($act,array('save','preview','edit'))){
    if($INFO['exists']){
      if($act == 'edit'){
        //the edit function will check again and do a source show
        //when no AUTH_EDIT available
        $permneed = AUTH_READ;
      }else{
        $permneed = AUTH_EDIT;
      }
    }else{
      $permneed = AUTH_CREATE;
    }
  }elseif(in_array($act,array('login','search','recent','profile'))){
    $permneed = AUTH_NONE;
  }elseif($act == 'register'){
    if ($conf['openregister']){
      $permneed = AUTH_NONE;
    }else{
      $permneed = AUTH_ADMIN;
    }
  }elseif($act == 'admin'){
    $permneed = AUTH_ADMIN;
  }else{
    $permneed = AUTH_READ;
  }
  if($INFO['perm'] >= $permneed) return $act;

  return 'denied';
}

/**
 * Handle 'save'
 *
 * Checks for spam and conflicts and saves the page.
 * Does a redirect to show the page afterwards or
 * returns a new action.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_save($act){
  global $ID;
  global $DATE;
  global $PRE;
  global $TEXT;
  global $SUF;
  global $SUM;

  //spam check
  if(checkwordblock())
    return 'wordblock';
  //conflict check //FIXME use INFO
  if($DATE != 0 && @filemtime(wikiFN($ID)) > $DATE )
    return 'conflict';

  //save it
  saveWikiText($ID,con($PRE,$TEXT,$SUF,1),$SUM,$_REQUEST['minor']); //use pretty mode for con
  //unlock it
  unlock($ID);

  //show it
  session_write_close();
  header("Location: ".wl($ID,'',true));
  exit();
}

/**
 * Handle 'login', 'logout'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_auth($act){
  global $ID;
  global $INFO;

  //already logged in?
  if($_SERVER['REMOTE_USER'] && $act=='login')
    return 'show';

  //handle logout
  if($act=='logout'){
    $lockedby = checklock($ID); //page still locked?
    if($lockedby == $_SERVER['REMOTE_USER'])
      unlock($ID); //try to unlock

    // do the logout stuff
    auth_logoff();

    // rebuild info array
    $INFO = pageinfo();

    return 'login';
  }

  return $act;
}

/**
 * Handle 'edit', 'preview'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_edit($act){
  global $ID;

  //check if locked by anyone - if not lock for my self
  $lockedby = checklock($ID);
  if($lockedby) return 'locked';

  lock($ID);
  return $act;
}

/**
 * Handle 'edit', 'preview'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_export($act){
  global $ID;
  global $REV;

  // no renderer for this
  if($act == 'export_raw'){
    header('Content-Type: text/plain; charset=utf-8');
    print rawWiki($ID,$REV);
    exit;
  }

  // html export #FIXME what about the template's style?
  if($act == 'export_xhtml'){
    global $conf;
    global $lang;
    header('Content-Type: text/html; charset=utf-8');
    ptln('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"');
    ptln(' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
    ptln('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$conf['lang'].'"');
    ptln(' lang="'.$conf['lang'].'" dir="'.$lang['direction'].'">');
    ptln('<head>');
    ptln('  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
    ptln('  <title>'.$ID.'</title>');
    tpl_metaheaders();
    ptln('</head>');
    ptln('<body>');
    ptln('<div class="dokuwiki export">');
    print p_wiki_xhtml($ID,$REV,false);
    ptln('</div>');
    ptln('</body>');
    ptln('</html>');
    exit;
  }

  // html body only
  if($act == 'export_xhtmlbody'){
    print p_wiki_xhtml($ID,$REV,false);
    exit;
  }

  // try to run renderer #FIXME use cached instructions
  $mode = substr($act,7);
  $text = p_render($mode,p_get_instructions(rawWiki($ID,$REV)),$info);
  if(!is_null($text)){
    print $text;
    exit;
  }



  return 'show';
}

/**
 * Handle 'subscribe', 'unsubscribe'
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 * @todo   localize
 */
function act_subscription($act){
  global $ID;
  global $INFO;
  global $lang;

  $file=metaFN($ID,'.mlist');
  if ($act=='subscribe' && !$INFO['subscribed']){
    if ($INFO['userinfo']['mail']){
      if (io_saveFile($file,$_SERVER['REMOTE_USER']."\n",true)) {
        $INFO['subscribed'] = true;
        msg(sprintf($lang[$act.'_success'], $INFO['userinfo']['name'], $ID),1);
      } else {
        msg(sprintf($lang[$act.'_error'], $INFO['userinfo']['name'], $ID),1);
      }
    } else {
      msg($lang['subscribe_noaddress']);
    }
  } elseif ($act=='unsubscribe' && $INFO['subscribed']){
    if (io_deleteFromFile($file,$_SERVER['REMOTE_USER']."\n")) {
      $INFO['subscribed'] = false;
      msg(sprintf($lang[$act.'_success'], $INFO['userinfo']['name'], $ID),1);
    } else {
      msg(sprintf($lang[$act.'_error'], $INFO['userinfo']['name'], $ID),1);
    }
  }

  return 'show';
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
