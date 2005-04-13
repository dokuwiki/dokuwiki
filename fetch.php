<?php
/**
 * DokuWiki media passthrough file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */


	if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/');
	require_once(DOKU_INC.'inc/init.php');
	require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/auth.php');

	//get input
	$MEDIA  = $_REQUEST['media'];
	$CACHE  = calc_cache($_REQUEST['cache']);
	$WIDTH  = $_REQUEST['w'];
	$HEIGHT = $_REQUEST['h'];
  $EXT    = media_extension($MEDIA);

	//media to local file
	if(preg_match('#^(https?|ftp)://#i',$MEDIA)){
    //handle external media
  	$FILE = get_from_URL($MEDIA,$EXT,$CACHE);
    if(!$FILE){
      //download failed - redirect to original URL
      header('Location: '.$MEDIA);
      exit;
    }
  }else{
    $MEDIA = cleanID($MEDIA);
    if(empty($MEDIA)){
      header("HTTP/1.0 400 Bad Request");
      print 'Bad request';
      exit;
    }

    //check permissions (namespace only)
    if(auth_quickaclcheck(getNS($MEDIA).':X') < AUTH_READ){
      header("HTTP/1.0 401 Unauthorized");
      //fixme add some image for imagefiles else display login message
      exit;
    }
    $FILE  = mediaFN($MEDIA);
  } 
  
  //check file existance
  if(!@file_exists($FILE)){
    header("HTTP/1.0 404 Not Found");
    //FIXME add some default broken image or display message
    exit;
  }



  //FIXME handle image resizing


  //FIXME add correct mimetype
  //FIXME send Size header
  //FIXME send Lastmod Handler
  //FIXME cache headers??
  //FIXME handle conditional and partial requests

  //send file
  passthru($FILE) ;


/* ----------- */

/**
 * Returns the wanted cachetime in seconds
 *
 * Resolves named constants
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function calc_cache($cache){
  global $conf;

  if(strtolower($cache) == 'nocache') return 0; //never cache
  if(strtolower($cache) == 'recache') return $conf['cachetime']; //use standard cache
  return -1; //cache endless
}

/**
 * Download a remote file and return local filename
 *
 * returns false if download fails. Uses cached file if available and
 * wanted
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function get_from_URL($url,$ext,$cache){
  global $conf;

  $url = strtolower($url);
  $md5 = md5($url);

  $local = $conf['mediadir']."/_cache/$md5.$ext";
  $mtime = @filemtime($local); // 0 if not exists

  //decide if download needed:

  //  never cache     exists but no endless cache     not exists or expired
  if( $cache == 0 || ($mtime != 0 && $cache != -1) || $mtime < time()-$cache ){
    if(download($url,$local)){
      return $local;
    }else{
      return false;
    }
  }
      
  //if cache exists use it else
  if($mtime) return $local;

  //else return false
  return false;
}




//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
