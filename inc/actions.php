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

  //check permissions
  $ACT = act_permcheck($ACT);

  //login stuff
  if(in_array($ACT,array('login','logout','register')))
    $ACT = act_auth($ACT);
 
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

  //check if searchword was given - else just show
  if($ACT == 'search' && empty($QUERY)){
    $ACT = 'show';
  }

  //handle admin tasks
  if($ACT == 'admin'){
		if($_REQUEST['page'] == 'acl'){
			require_once(DOKU_INC.'inc/admin_acl.php');
			admin_acl_handler();
		}
  }

  //call template FIXME: all needed vars available?
  header('Content-Type: text/html; charset=utf-8'); 
  include(DOKU_INC.'tpl/'.$conf['template'].'/main.php');
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

  if($act == 'register' && !$conf['openregister'])
    return 'show';

  if($act == 'export_html') $act = 'export_xhtml';

  if(array_search($act,array('login','logout','register','save','edit',
                             'preview','search','show','check','index','revisions',
                             'diff','recent','backlink','admin',)) === false
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

  if(in_array($act,array('save','preview','edit'))){
    if($INFO['exists']){
      $permneed = AUTH_EDIT;
    }else{
      $permneed = AUTH_CREATE;
    }
  }elseif(in_array($act,array('login','register','search','recent'))){
    $permneed = AUTH_NONE;
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
  saveWikiText($ID,con($PRE,$TEXT,$SUF,1),$SUM); //use pretty mode for con
  //unlock it
  unlock($ID);
      
  //show it
  session_write_close();
  header("Location: ".wl($ID,'',true));
  exit();
}

/**
 * Handle 'login', 'logout', 'register'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_auth($act){
  //already logged in?
  if($_SERVER['REMOTE_USER'] && $act=='login')
    return 'show';

  //handle logout
  if($act=='logout'){
    auth_logoff();
    return 'login';
  }

  //handle register
  if($act=='register' && register()){
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
    header('Content-Type: text/html; charset=utf-8');
    ptln('<html>');
    ptln('<head>');
    tpl_metaheaders();
    ptln('</head>');
    ptln('<body>');
    print p_wiki_xhtml($ID,$REV,false);
    ptln('</body>');
    ptln('</html>');
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


//Setup VIM: ex: et ts=2 enc=utf-8 :
