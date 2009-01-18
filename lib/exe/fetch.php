<?php
/**
 * DokuWiki media passthrough file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
  define('DOKU_DISABLE_GZIP_OUTPUT', 1);
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/media.php');
  require_once(DOKU_INC.'inc/pageutils.php');
  require_once(DOKU_INC.'inc/confutils.php');
  require_once(DOKU_INC.'inc/auth.php');

  //close sesseion
  session_write_close();
  if(!defined('CHUNK_SIZE')) define('CHUNK_SIZE',16*1024);

  $mimetypes = getMimeTypes();

  //get input
  $MEDIA  = stripctl(getID('media',false)); // no cleaning except control chars - maybe external
  $CACHE  = calc_cache($_REQUEST['cache']);
  $WIDTH  = (int) $_REQUEST['w'];
  $HEIGHT = (int) $_REQUEST['h'];
  list($EXT,$MIME,$DL) = mimetype($MEDIA);
  if($EXT === false){
    $EXT  = 'unknown';
    $MIME = 'application/octet-stream';
    $DL   = true;
  }

  //media to local file
  if(preg_match('#^(https?)://#i',$MEDIA)){
    //handle external images
    if(strncmp($MIME,'image/',6) == 0) $FILE = media_get_from_URL($MEDIA,$EXT,$CACHE);
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

  $ORIG = $FILE;

  //handle image resizing/cropping
  if((substr($MIME,0,5) == 'image') && $WIDTH){
    if($HEIGHT){
        $FILE = media_crop_image($FILE,$EXT,$WIDTH,$HEIGHT);
    }else{
        $FILE = media_resize_image($FILE,$EXT,$WIDTH,$HEIGHT);
    }
  }

  // finally send the file to the client
  $data = array('file'     => $FILE,
                'mime'     => $MIME,
                'download' => $DL,
                'cache'    => $CACHE,
                'orig'     => $ORIG,
                'ext'      => $EXT,
                'width'    => $WIDTH,
                'height'   => $HEIGHT);

  $evt = new Doku_Event('MEDIA_SENDFILE', $data);
  if ($evt->advise_before()) {
    sendFile($data['file'],$data['mime'],$data['download'],$data['cache']);
  }

/* ------------------------------------------------------------------------ */

/**
 * Set headers and send the file to the client
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function sendFile($file,$mime,$dl,$cache){
  global $conf;
  $fmtime = @filemtime($file);
  // send headers
  header("Content-Type: $mime");
  // smart http caching headers
  if ($cache==-1) {
    // cache
    // cachetime or one hour
    header('Expires: '.gmdate("D, d M Y H:i:s", time()+max($conf['cachetime'], 3600)).' GMT');
    header('Cache-Control: public, proxy-revalidate, no-transform, max-age='.max($conf['cachetime'], 3600));
    header('Pragma: public');
  } else if ($cache>0) {
    // recache
    // remaining cachetime + 10 seconds so the newly recached media is used
    header('Expires: '.gmdate("D, d M Y H:i:s", $fmtime+$conf['cachetime']+10).' GMT');
    header('Cache-Control: public, proxy-revalidate, no-transform, max-age='.max($fmtime-time()+$conf['cachetime']+10, 0));
    header('Pragma: public');
  } else if ($cache==0) {
    // nocache
    header('Cache-Control: must-revalidate, no-transform, post-check=0, pre-check=0');
    header('Pragma: public');
  }
  //send important headers first, script stops here if '304 Not Modified' response
  http_conditionalRequest($fmtime);


  //download or display?
  if($dl){
    header('Content-Disposition: attachment; filename="'.basename($file).'";');
  }else{
    header('Content-Disposition: inline; filename="'.basename($file).'";');
  }

  //use x-sendfile header to pass the delivery to compatible webservers
  if (http_sendfile($file)) exit;

  //support download continueing
  header('Accept-Ranges: bytes');
  list($start,$len) = http_rangeRequest(filesize($file));

  // send file contents
  $fp = @fopen($file,"rb");
  if($fp){
    fseek($fp,$start); //seek to start of range

    $chunk = ($len > CHUNK_SIZE) ? CHUNK_SIZE : $len;
    while (!feof($fp) && $chunk > 0) {
      @set_time_limit(30); // large files can take a lot of time
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

//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
