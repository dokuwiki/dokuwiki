<?php
/**
 * DokuWiki mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  define('DOKUWIKIVERSION','2005-01-14');

  ini_set('short_open_tag',"1");
  require_once("conf/dokuwiki.php");
  require_once("inc/common.php");
  require_once("inc/html.php");
  require_once("inc/parser.php");
  require_once("lang/en/lang.php");
  require_once("lang/".$conf['lang']."/lang.php");
  setCorrectLocale();
  require_once("inc/auth.php");

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

  header('Content-Type: text/html; charset='.$lang['encoding']);

  if($ACT == 'debug'){
    html_debug();
    exit;
  }

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
      header("Location: ".wl($ID, '','doku.php',true));
      exit();
    }
  }

  //make infos about current page available
  $INFO = pageinfo();

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

  //check which permission is needed
  if(in_array($ACT,array('preview','wordblock','conflict','lockedby'))){
    if($INFO['exists']){
      $permneed = AUTH_EDIT;
    }else{
      $permneed = AUTH_CREATE;
    }
  }elseif(in_array($ACT,array('revisions','show','edit'))){
    $permneed = AUTH_READ;
  }else{
    $permneed = AUTH_NONE;
  }

  //start output
  if(substr($ACT,0,6) != 'export') html_header();
  if(html_acl($permneed)){
    if($ACT == 'edit'){
      html_edit();
    }elseif($ACT == $lang['btn_preview']){
      html_edit($TEXT);
      html_show($TEXT);
    }elseif($ACT == 'wordblock'){
      html_edit($TEXT,'wordblock');
    }elseif($ACT == 'search' && !empty($QUERY)){
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

?>
