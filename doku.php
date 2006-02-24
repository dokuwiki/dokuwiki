<?php
/**
 * DokuWiki mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//  xdebug_start_profiling();

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/pageutils.php');
  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/auth.php');
  require_once(DOKU_INC.'inc/actions.php');

  //import variables
  $QUERY = trim($_REQUEST['id']);
  $ID    = getID();
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

  //sanitize revision
  $REV = preg_replace('/[^0-9]/','',$REV);

  //we accept the do param as HTTP header, too:
  if(!empty($_SERVER['HTTP_X_DOKUWIKI_DO'])){
    $ACT = trim(strtolower($_SERVER['HTTP_X_DOKUWIKI_DO']));
  }

  if(!empty($IDX)) $ACT='index';
  //set default #FIXME not needed here? done in actions?
  if(empty($ACT)) $ACT = 'show';


  if($conf['allowdebug'] && $ACT == 'debug'){
    html_debug();
    exit;
  }

  //make infos about the selected page available
  $INFO = pageinfo();

  //send 404 for missing pages if configured
  if($conf['send404'] && !$INFO['exists']){
    header('HTTP/1.0 404 Not Found');
  }

  //prepare breadcrumbs (initialize a static var)
  breadcrumbs();

  //close session
  session_write_close();

  //do the work
  act_dispatch($ACT);

//  xdebug_dump_function_profile(1);
?>
