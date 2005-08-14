<?php
  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
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

  $ERROR = false;
  // check image permissions
  $AUTH = auth_quickaclcheck($IMG);
  if($AUTH >= AUTH_READ){
    // check if image exists
		$SRC = mediaFN($IMG);
    if(!file_exists($SRC)){
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
  
  //restore old umask
  umask($conf['oldumask']);

?>
