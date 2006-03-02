<?php
/**
 * DokuWiki media passthrough file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/pageutils.php');
  require_once(DOKU_INC.'inc/confutils.php');
  require_once(DOKU_INC.'inc/auth.php');
  //close sesseion
  session_write_close();
  if(!defined('CHUNK_SIZE')) define('CHUNK_SIZE',16*1024);

  $mimetypes = getMimeTypes();

  //get input
  $MEDIA  = getID('media',false); // no cleaning - maybe external
  $CACHE  = calc_cache($_REQUEST['cache']);
  $WIDTH  = $_REQUEST['w'];
  $HEIGHT = $_REQUEST['h'];
  list($EXT,$MIME) = mimetype($MEDIA);
  if($EXT === false){
    $EXT  = 'unknown';
    $MIME = 'application/octet-stream';
  }

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
      //fixme add some image for imagefiles
      print 'Unauthorized';
      exit;
    }
    $FILE  = mediaFN($MEDIA);
  }

  //check file existance
  if(!@file_exists($FILE)){
    header("HTTP/1.0 404 Not Found");
    //FIXME add some default broken image
    print 'Not Found';
    exit;
  }

  //handle image resizing
  if((substr($MIME,0,5) == 'image') && $WIDTH){
    $FILE = get_resized($FILE,$EXT,$WIDTH,$HEIGHT);
  }

  // finally send the file to the client
  sendFile($FILE,$MIME);

/* ------------------------------------------------------------------------ */

/**
 * Set headers and send the file to the client
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function sendFile($file,$mime){
  // send headers
  header("Content-Type: $mime");
  http_conditionalRequest(filemtime($file));
  list($start,$len) = http_rangeRequest(filesize($file));
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
  header('Accept-Ranges: bytes');

  //application mime type is downloadable
  if(substr($mime,0,11) == 'application'){
    header('Content-Disposition: attachment; filename="'.basename($file).'";');
  }

  // send file contents
  $fp = @fopen($file,"rb");
  if($fp){
    fseek($fp,$start); //seek to start of range

    $chunk = ($len > CHUNK_SIZE) ? CHUNK_SIZE : $len;
    while (!feof($fp) && $chunk > 0) {
      @set_time_limit(); // large files can take a lot of time
      print fread($fp, $chunk);
      flush();
      $len -= $chunk;
      $chunk = ($len > CHUNK_SIZE) ? CHUNK_SIZE : $len;
    }
    fclose($fp);
  }else{
    header("HTTP/1.0 500 Internal Server Error");
    print "Could not read $file - bad permissions?";
  }
}

/**
 * Checks and sets headers to handle range requets
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @returns array The start byte and the amount of bytes to send
 */
function http_rangeRequest($size){
  if(!isset($_SERVER['HTTP_RANGE'])){
    // no range requested - send the whole file
    header("Content-Length: $size");
    return array(0,$size);
  }

  $t = explode('=', $_SERVER['HTTP_RANGE']);
  if (!$t[0]=='bytes') {
    // we only understand byte ranges - send the whole file
    header("Content-Length: $size");
    return array(0,$size);
  }

  $r = explode('-', $t[1]);
  $start = (int)$r[0];
  $end = (int)$r[1];
  if (!$end) $end = $size - 1;
  if ($start > $end || $start > $size || $end > $size){
    header('HTTP/1.1 416 Requested Range Not Satisfiable');
    print 'Bad Range Request!';
    exit;
  }

  $tot = $end - $start + 1;
  header('HTTP/1.1 206 Partial Content');
  header("Content-Range: bytes {$start}-{$end}/{$size}");
  header("Content-Length: $tot");

  return array($start,$tot);
}

/**
 * Checks and sets HTTP headers for conditional HTTP requests
 *
 * @author Simon Willison <swillison@gmail.com>
 * @link   http://simon.incutio.com/archive/2003/04/23/conditionalGet
 */
function http_conditionalRequest($timestamp){
    // A PHP implementation of conditional get, see 
    //   http://fishbowl.pastiche.org/archives/001132.html
    $last_modified = substr(date('r', $timestamp), 0, -5).'GMT';
    $etag = '"'.md5($last_modified).'"';
    // Send the headers
    header("Last-Modified: $last_modified");
    header("ETag: $etag");
    // See if the client has provided the required headers
    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
        stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
        false;
    $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
        stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : 
        false;
    if (!$if_modified_since && !$if_none_match) {
        return;
    }
    // At least one of the headers is there - check them
    if ($if_none_match && $if_none_match != $etag) {
        return; // etag is there but doesn't match
    }
    if ($if_modified_since && $if_modified_since != $last_modified) {
        return; // if-modified-since is there but doesn't match
    }
    // Nothing has changed since their last request - serve a 304 and exit
    header('HTTP/1.0 304 Not Modified');
    exit;
}

/**
 * Resizes the given image to the given size
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function get_resized($file, $ext, $w, $h=0){
  global $conf;

  $info  = getimagesize($file);
  if(!$h) $h = round(($w * $info[1]) / $info[0]);


  //cache
  $local = getCacheName($file,'.media.'.$w.'x'.$h.'.'.$ext);
  $mtime = @filemtime($local); // 0 if not exists

  if( $mtime > filemtime($file) ||
      resize_imageIM($ext,$file,$info[0],$info[1],$local,$w,$h) ||
      resize_imageGD($ext,$file,$info[0],$info[1],$local,$w,$h) ){
    return $local;
  }
  //still here? resizing failed
  return $file;
}

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
 * @author  Pavel Vitis <Pavel.Vitis@seznam.cz>
 */
function get_from_URL($url,$ext,$cache){
  global $conf;

  $local = getCacheName(strtolower($url),".media.$ext");
  $mtime = @filemtime($local); // 0 if not exists

  //decide if download needed:
  if( $cache == 0 ||                             // never cache
      ($mtime != 0 && $cache != -1) ||           // exists but no endless cache
      ($mtime == 0) ||                           // not exists
      ($cache != -1 && $mtime < time()-$cache)   // expired
    ){
      if(io_download($url,$local)){
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

/**
 * resize images using external ImageMagick convert program
 *
 * @author Pavel Vitis <Pavel.Vitis@seznam.cz>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resize_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h){
  global $conf;

  // check if convert is configured
  if(!$conf['im_convert']) return false;

  // prepare command
  $cmd  = $conf['im_convert'];
  $cmd .= ' -resize '.$to_w.'x'.$to_h.'!';
  $cmd .= " $from $to";

  @exec($cmd,$out,$retval);
  if ($retval == 0) return true;

  return false;
}

/**
 * resize images using PHP's libGD support
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resize_imageGD($ext,$from,$from_w,$from_h,$to,$to_w,$to_h){
  global $conf;

  if($conf['gdlib'] < 1) return false; //no GDlib available or wanted

  // check available memory
  if(!is_mem_available(($from_w * $from_h * 4) + ($to_w * $to_h * 4))){
    return false;
  }

  // create an image of the given filetype
  if ($ext == 'jpg' || $ext == 'jpeg'){
    if(!function_exists("imagecreatefromjpeg")) return false;
    $image = @imagecreatefromjpeg($from);
  }elseif($ext == 'png') {
    if(!function_exists("imagecreatefrompng")) return false;
    $image = @imagecreatefrompng($from);

  }elseif($ext == 'gif') {
    if(!function_exists("imagecreatefromgif")) return false;
    $image = @imagecreatefromgif($from);
  }
  if(!$image) return false;

  if(($conf['gdlib']>1) && function_exists("imagecreatetruecolor")){
    $newimg = @imagecreatetruecolor ($to_w, $to_h);
  }
  if(!$newimg) $newimg = @imagecreate($to_w, $to_h);
  if(!$newimg){
    imagedestroy($image);
    return false;
  }

  //keep png alpha channel if possible
  if($ext == 'png' && $conf['gdlib']>1 && function_exists('imagesavealpha')){
    imagealphablending($newimg, false);
    imagesavealpha($newimg,true);
  }

  //try resampling first
  if(function_exists("imagecopyresampled")){
    if(!@imagecopyresampled($newimg, $image, 0, 0, 0, 0, $to_w, $to_h, $from_w, $from_h)) {
      imagecopyresized($newimg, $image, 0, 0, 0, 0, $to_w, $to_h, $from_w, $from_h);
    }
  }else{
    imagecopyresized($newimg, $image, 0, 0, 0, 0, $to_w, $to_h, $from_w, $from_h);
  }

  $okay = false;
  if ($ext == 'jpg' || $ext == 'jpeg'){
    if(!function_exists('imagejpeg')){
      $okay = false;
    }else{
      $okay = imagejpeg($newimg, $to, 70);
    }
  }elseif($ext == 'png') {
    if(!function_exists('imagepng')){
      $okay = false;
    }else{
      $okay =  imagepng($newimg, $to);
    }
  }elseif($ext == 'gif') {
    if(!function_exists('imagegif')){
      $okay = false;
    }else{
      $okay = imagegif($newimg, $to);
    }
  }

  // destroy GD image ressources
  if($image) imagedestroy($image);
  if($newimg) imagedestroy($newimg);

  return $okay;
}

/**
 * Checks if the given amount of memory is available
 *
 * If the memory_get_usage() function is not available the
 * function just assumes $used bytes of already allocated memory
 *
 * @param  int $mem  Size of memory you want to allocate in bytes
 * @param  int $used already allocated memory (see above)
 * @author Filip Oscadal <webmaster@illusionsoftworks.cz>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function is_mem_available($mem,$bytes=1048576){
  $limit = trim(ini_get('memory_limit'));
  if(empty($limit)) return true; // no limit set!

  // parse limit to bytes
  $unit = strtolower(substr($limit,-1));
  switch($unit){
    case 'g':
      $limit = substr($limit,0,-1);
      $limit *= 1024*1024*1024;
      break;
    case 'm':
      $limit = substr($limit,0,-1);
      $limit *= 1024*1024;
      break;
    case 'k':
      $limit = substr($limit,0,-1);
      $limit *= 1024;
      break;
  }

  // get used memory if possible
  if(function_exists('memory_get_usage')){
    $used = memory_get_usage();
  }


  if($used+$mem > $limit){
    return false;
  }

  return true;
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
