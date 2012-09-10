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

  //close session
  session_write_close();

  $mimetypes = getMimeTypes();

  //get input
  $MEDIA  = stripctl(getID('media',false)); // no cleaning except control chars - maybe external
  $CACHE  = calc_cache($_REQUEST['cache']);
  $WIDTH  = (int) $_REQUEST['w'];
  $HEIGHT = (int) $_REQUEST['h'];
  $REV   = (int) @$_REQUEST['rev'];
  //sanitize revision
  $REV = preg_replace('/[^0-9]/','',$REV);

  list($EXT,$MIME,$DL) = mimetype($MEDIA,false);
  if($EXT === false){
    $EXT  = 'unknown';
    $MIME = 'application/octet-stream';
    $DL   = true;
  }

  // check for permissions, preconditions and cache external files
  list($STATUS, $STATUSMESSAGE) = checkFileStatus($MEDIA, $FILE, $REV);

  // prepare data for plugin events
  $data = array('media'           => $MEDIA,
                'file'            => $FILE,
                'orig'            => $FILE,
                'mime'            => $MIME,
                'download'        => $DL,
                'cache'           => $CACHE,
                'ext'             => $EXT,
                'width'           => $WIDTH,
                'height'          => $HEIGHT,
                'status'          => $STATUS,
                'statusmessage'   => $STATUSMESSAGE,
  );

  // handle the file status
  $evt = new Doku_Event('FETCH_MEDIA_STATUS', $data);
  if ( $evt->advise_before() ) {
    // redirects
    if($data['status'] > 300 && $data['status'] <= 304){
      send_redirect($data['statusmessage']);
    }
    // send any non 200 status
    if($data['status'] != 200){
      header('HTTP/1.0 ' . $data['status'] . ' ' . $data['statusmessage']);
    }
    // die on errors
    if($data['status'] > 203){
      print $data['statusmessage'];
      exit;
    }
  }
  $evt->advise_after();
  unset($evt);

  //handle image resizing/cropping
  if((substr($MIME,0,5) == 'image') && $WIDTH){
    if($HEIGHT){
        $data['file'] = $FILE = media_crop_image($data['file'],$EXT,$WIDTH,$HEIGHT);
    }else{
        $data['file'] = $FILE  = media_resize_image($data['file'],$EXT,$WIDTH,$HEIGHT);
    }
  }

  // finally send the file to the client
  $evt = new Doku_Event('MEDIA_SENDFILE', $data);
  if ($evt->advise_before()) {
    sendFile($data['file'],$data['mime'],$data['download'],$data['cache']);
  }
  // Do something after the download finished.
  $evt->advise_after();

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

  // send file contents
  $fp = @fopen($file,"rb");
  if($fp){
    http_rangeRequest($fp,filesize($file),$mime);
  }else{
    header("HTTP/1.0 500 Internal Server Error");
    print "Could not read $file - bad permissions?";
  }
}

/**
 * Check for media for preconditions and return correct status code
 *
 * READ: MEDIA, MIME, EXT, CACHE
 * WRITE: MEDIA, FILE, array( STATUS, STATUSMESSAGE )
 *
 * @author Gerry Weissbach <gerry.w@gammaproduction.de>
 * @param $media reference to the media id
 * @param $file reference to the file variable
 * @returns array(STATUS, STATUSMESSAGE)
 */
function checkFileStatus(&$media, &$file, $rev='') {
  global $MIME, $EXT, $CACHE;

  //media to local file
  if(preg_match('#^(https?)://#i',$media)){
    //check hash
    if(substr(md5(auth_cookiesalt().$media),0,6) != $_REQUEST['hash']){
      return array( 412, 'Precondition Failed');
    }
    //handle external images
    if(strncmp($MIME,'image/',6) == 0) $file = media_get_from_URL($media,$EXT,$CACHE);
    if(!$file){
      //download failed - redirect to original URL
      return array( 302, $media );
    }
  }else{
    $media = cleanID($media);
    if(empty($media)){
      return array( 400, 'Bad request' );
    }

    //check permissions (namespace only)
    if(auth_quickaclcheck(getNS($media).':X') < AUTH_READ){
      return array( 403, 'Forbidden' );
    }
    $file  = mediaFN($media, $rev);
  }

  //check file existance
  if(!@file_exists($file)){
      return array( 404, 'Not Found' );
  }

  return array(200, null);
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

//Setup VIM: ex: et ts=2 :
