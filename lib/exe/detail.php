<?php
  if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
  define('DOKU_MEDIADETAIL',1);
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/lang/en/lang.php');
  require_once(DOKU_INC.'inc/lang/'.$conf['lang'].'/lang.php');
  require_once(DOKU_INC.'inc/JpegMeta.php');
  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/template.php');
  require_once(DOKU_INC.'inc/auth.php');
  //close session
  session_write_close();

  $IMG  = getID('media');
  $ID   = cleanID($_REQUEST['id']);

  if($conf['allowdebug'] && $_REQUEST['debug']){
      print '<pre>';
      foreach(explode(' ','basedir userewrite baseurl useslash') as $x){
          print '$'."conf['$x'] = '".$conf[$x]."';\n";
      }
      foreach(explode(' ','DOCUMENT_ROOT HTTP_HOST SCRIPT_FILENAME PHP_SELF '.
                      'REQUEST_URI SCRIPT_NAME PATH_INFO PATH_TRANSLATED') as $x){
          print '$'."_SERVER['$x'] = '".$_SERVER[$x]."';\n";
      }
      print "getID('media'): ".getID('media')."\n";
      print "getID('media',false): ".getID('media',false)."\n";
      print '</pre>';
  }

  $ERROR = false;
  // check image permissions
  $AUTH = auth_quickaclcheck($IMG);
  if($AUTH >= AUTH_READ){
    // check if image exists
    $SRC = mediaFN($IMG);
    if(!@file_exists($SRC)){
      //doesn't exist!

    }
  }else{
    // no auth
    $ERROR = p_locale_xhtml('denied');
  }

  /*if(!$ERROR){
    // load EXIF/IPTC/image details
    $INFO = array();
    $INFO['std']['']
    imagesize
  }*/


  //start output and load template
  header('Content-Type: text/html; charset=utf-8');
  include(template('detail.php'));

?>
