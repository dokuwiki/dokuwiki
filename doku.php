<?php
/**
 * DokuWiki mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/parser.php');
  require_once(DOKU_INC.'lang/en/lang.php');
  require_once(DOKU_INC.'lang/'.$conf['lang'].'/lang.php');
  require_once(DOKU_INC.'inc/auth.php');

  //import variables
  $QUERY = trim($_REQUEST['id']);
  $ID    = cleanID($_REQUEST['id']);
  $REV   = $_REQUEST['rev'];
  $ACT   = $_REQUEST['do'];
  $IDX   = $_REQUEST['idx'];
  $DATE  = $_REQUEST['date'];
  $RANGE = $_REQUEST['lines'];
  $HIGH  = $_REQUEST['s'];
  if(empty($HIGH)) $HIGH = getGoogleQuery();
  
#  $ACL_USER     = urldecode($_REQUEST['acl_user']);
#  $ACL_SCOPE    = urldecode($_REQUEST['acl_scope']);
#  $ACL_LEVEL    = $_REQUEST['acl_level'];
#  $ACL_CHECKBOX = $_REQUEST['acl_checkbox'];

  $TEXT  = cleanText($_POST['wikitext']);
  $PRE   = cleanText($_POST['prefix']);
  $SUF   = cleanText($_POST['suffix']);
  $SUM   = $_REQUEST['summary'];

  //we accept the do param as HTTP header, too:
  if(!empty($_SERVER['HTTP_X_DOKUWIKI_DO'])){
    $ACT = trim(strtolower($_SERVER['HTTP_X_DOKUWIKI_DO']));
  }

  if(!empty($IDX)) $ACT='index';
  //set defaults
  if(empty($ID))  $ID  = $conf['start'];
  if(empty($ACT)) $ACT = 'show';


  if($ACT == 'debug'){
    html_debug();
    exit;
  }
  
  //make infos about the selected page available
  $INFO = pageinfo();

  //already logged in?
  if($_SERVER['REMOTE_USER'] && $ACT=='login') $ACT='show';
  //handle logout
  if($ACT=='logout'){
    auth_logoff();
    $ACT='login';
  }

  //handle register
  if($ACT=='register' && register()){
    $ACT='login';
  }

/*
  //handle acl_admin stuff, add acl entry
  if( ($ACT=='acl_admin_add') && (auth_quickaclcheck($ID) == AUTH_GRANT)){
    acl_admin_change($ACL_SCOPE, $ACL_USER, "", $ACL_CHECKBOX);
    # reload ACL into a global array
    //$AUTH_ACL = file('conf/acl.auth');
    $AUTH_ACL = load_acl_config();
    $ACT='acl_admin';
  }
  
  //handle acl_admin stuff, change acl entry
  if( ($ACT=='acl_admin_change') && (auth_quickaclcheck($ID) == AUTH_GRANT)){
    acl_admin_change($ACL_SCOPE, $ACL_USER, $ACL_LEVEL, $ACL_CHECKBOX);
    # reload ACL into a global array
    $AUTH_ACL = load_acl_config();
    $ACT='acl_admin';
  }
  
  //handle acl_admin_del stuff, remove acl entry
  if( ($ACT=='acl_admin_del') && (auth_quickaclcheck($ID) == AUTH_GRANT)) {
    acl_admin_del($ACL_SCOPE, $ACL_USER, $ACL_LEVEL);
    # reload ACL into a global array
    $AUTH_ACL = load_acl_config();
    $ACT='acl_admin';
  }
*/

  //do saving after spam- and conflictcheck
  if($ACT == $lang['btn_save'] && auth_quickaclcheck($ID)){
    if(checkwordblock()){
      //spam detected
      $ACT = 'wordblock';
    }elseif($DATE != 0 && @filemtime(wikiFN($ID)) > $DATE ){
      //newer version available -> ask what to do
      $ACT = 'conflict';
    }else{
      //save it
      saveWikiText($ID,con($PRE,$TEXT,$SUF,1),$SUM); //use pretty mode for con
      //unlock it
      unlock($id);
      //show it
      header("Location: ".wl($ID,'',true));
      exit();
    }
  }

  //Editing: check if locked by anyone - if not lock for my self
  if(($ACT == 'edit' || $ACT == $lang['btn_preview']) && $INFO['editable']){
    $lockedby = checklock($ID);
    if($lockedby){
      $ACT = 'locked';
    }else{
      lock($ID);
    }
  }else{
    //try to unlock
    unlock($ID);
  }


  //display some infos
  if($ACT == 'check'){
    check();
    $ACT = 'show';
  }

  //check if searchword was given - else just show
  if($ACT == 'search' && empty($QUERY)){
    $ACT = 'show';
  }

  //check which permission is needed
  if(in_array($ACT,array('preview','wordblock','conflict','lockedby'))){
    if($INFO['exists']){
      $permneed = AUTH_EDIT;
    }else{
      $permneed = AUTH_CREATE;
    }
  }elseif(in_array($ACT,array('login','register','search','recent'))){
    $permneed = AUTH_NONE;
  }else{
    $permneed = AUTH_READ;
  }

  //start output
  header('Content-Type: text/html; charset='.$lang['encoding']);
  if(substr($ACT,0,6) != 'export') html_header();
  if(html_acl($permneed)){
    if($ACT == 'edit'){
      html_edit();
    }elseif($ACT == $lang['btn_preview']){
      html_edit($TEXT);
      html_show($TEXT);
    }elseif($ACT == 'wordblock'){
      html_edit($TEXT,'wordblock');
    }elseif($ACT == 'search'){
      html_search();
    }elseif($ACT == 'revisions'){
      html_revisions();
    }elseif($ACT == 'diff'){
      html_diff();
    }elseif($ACT == 'recent'){
      html_recent();
    }elseif($ACT == 'index'){
      html_index($IDX);
    }elseif($ACT == 'backlink'){
      html_backlinks();
    }elseif($ACT == 'conflict'){
      html_conflict(con($PRE,$TEXT,$SUF),$SUM);
      html_diff(con($PRE,$TEXT,$SUF),false);
    }elseif($ACT == 'locked'){
      html_locked($lockedby);
#    }elseif( ($ACT == 'acl_admin') && (auth_quickaclcheck($ID) == AUTH_GRANT)){
#      html_acl_admin();
    }elseif($ACT == 'login'){
      html_login();
    }elseif($ACT == 'register' && $conf['openregister']){
      html_register();
    }elseif($ACT == 'export_html'){
      html_head();
			print "<body>\n";
			print parsedWiki($ID,$REV,false);
			print "</body>\n</html>\n";
		}elseif($ACT == 'export_raw'){
			header("Content-Type: text/plain");
      print rawWiki($ID,$REV);
    }else{
      $ACT='show';
      html_show();
    }
  }
  if(substr($ACT,0,6) != 'export') html_footer();


  //restore old umask
  umask($conf['oldumask']);
?>
