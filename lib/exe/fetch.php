<?php
/**
 * DokuWiki media passthrough file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__).'/../../');
define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC.'inc/init.php');
session_write_close(); //close session

// BEGIN main (if not testing)
if(!defined('SIMPLE_TEST')) {
    $mimetypes = getMimeTypes();

    //get input
    $MEDIA  = stripctl(getID('media', false)); // no cleaning except control chars - maybe external
    $CACHE  = calc_cache($INPUT->str('cache'));
    $WIDTH  = $INPUT->int('w');
    $HEIGHT = $INPUT->int('h');
    $REV    = & $INPUT->ref('rev');
    //sanitize revision
    $REV = preg_replace('/[^0-9]/', '', $REV);

    list($EXT, $MIME, $DL) = mimetype($MEDIA, false);
    if($EXT === false) {
        $EXT  = 'unknown';
        $MIME = 'application/octet-stream';
        $DL   = true;
    }

    // check for permissions, preconditions and cache external files
    list($STATUS, $STATUSMESSAGE) = checkFileStatus($MEDIA, $FILE, $REV);

    // prepare data for plugin events
    $data = array(
        'media'         => $MEDIA,
        'file'          => $FILE,
        'orig'          => $FILE,
        'mime'          => $MIME,
        'download'      => $DL,
        'cache'         => $CACHE,
        'ext'           => $EXT,
        'width'         => $WIDTH,
        'height'        => $HEIGHT,
        'status'        => $STATUS,
        'statusmessage' => $STATUSMESSAGE,
        'ispublic'      => media_ispublic($MEDIA),
    );

    // handle the file status
    $evt = new Doku_Event('FETCH_MEDIA_STATUS', $data);
    if($evt->advise_before()) {
        // redirects
        if($data['status'] > 300 && $data['status'] <= 304) {
            send_redirect($data['statusmessage']);
        }
        // send any non 200 status
        if($data['status'] != 200) {
            http_status($data['status'], $data['statusmessage']);
        }
        // die on errors
        if($data['status'] > 203) {
            print $data['statusmessage'];
            exit;
        }
    }
    $evt->advise_after();
    unset($evt);

    //handle image resizing/cropping
    if((substr($MIME, 0, 5) == 'image') && $WIDTH) {
        if($HEIGHT) {
            $data['file'] = $FILE = media_crop_image($data['file'], $EXT, $WIDTH, $HEIGHT);
        } else {
            $data['file'] = $FILE = media_resize_image($data['file'], $EXT, $WIDTH, $HEIGHT);
        }
    }

    // finally send the file to the client
    $evt = new Doku_Event('MEDIA_SENDFILE', $data);
    if($evt->advise_before()) {
        sendFile($data['file'], $data['mime'], $data['download'], $data['cache'], $data['ispublic']);
    }
    // Do something after the download finished.
    $evt->advise_after();  // will not be emitted on 304 or x-sendfile

}// END DO main

/* ------------------------------------------------------------------------ */

/**
 * Set headers and send the file to the client
 *
 * The $cache parameter influences how long files may be kept in caches, the $public parameter
 * influences if this caching may happen in public proxis or in the browser cache only FS#2734
 *
 * This function will abort the current script when a 304 is sent or file sending is handled
 * through x-sendfile
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @param string $file   local file to send
 * @param string $mime   mime type of the file
 * @param bool   $dl     set to true to force a browser download
 * @param int    $cache  remaining cache time in seconds (-1 for $conf['cache'], 0 for no-cache)
 * @param bool   $public is this a public ressource or a private one?
 */
function sendFile($file, $mime, $dl, $cache, $public = false) {
    global $conf;
    // send mime headers
    header("Content-Type: $mime");

    // calculate cache times
    if($cache == -1) {
        $maxage  = max($conf['cachetime'], 3600); // cachetime or one hour
        $expires = time() + $maxage;
    } else if($cache > 0) {
        $maxage  = $cache; // given time
        $expires = time() + $maxage;
    } else { // $cache == 0
        $maxage  = 0;
        $expires = 0; // 1970-01-01
    }

    // smart http caching headers
    if($maxage) {
        if($public) {
            // cache publically
            header('Expires: '.gmdate("D, d M Y H:i:s", $expires).' GMT');
            header('Cache-Control: public, proxy-revalidate, no-transform, max-age='.$maxage);
            header('Pragma: public');
        } else {
            // cache in browser
            header('Expires: '.gmdate("D, d M Y H:i:s", $expires).' GMT');
            header('Cache-Control: private, no-transform, max-age='.$maxage);
            header('Pragma: private');
        }
    } else {
        // no cache at all
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Cache-Control: private, no-transform, max-age=0');
        header('Pragma: no-store');
    }

    //send important headers first, script stops here if '304 Not Modified' response
    $fmtime = @filemtime($file);
    http_conditionalRequest($fmtime);

    //download or display?
    if($dl) {
        header('Content-Disposition: attachment; filename="'.utf8_basename($file).'";');
    } else {
        header('Content-Disposition: inline; filename="'.utf8_basename($file).'";');
    }

    //use x-sendfile header to pass the delivery to compatible webservers
    if(http_sendfile($file)) exit;

    // send file contents
    $fp = @fopen($file, "rb");
    if($fp) {
        http_rangeRequest($fp, filesize($file), $mime);
    } else {
        http_status(500);
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
 * @param $file  reference to the file variable
 * @returns array(STATUS, STATUSMESSAGE)
 */
function checkFileStatus(&$media, &$file, $rev = '') {
    global $MIME, $EXT, $CACHE, $INPUT;

    //media to local file
    if(preg_match('#^(https?)://#i', $media)) {
        //check hash
        if(substr(md5(auth_cookiesalt().$media), 0, 6) !== $INPUT->str('hash')) {
            return array(412, 'Precondition Failed');
        }
        //handle external images
        if(strncmp($MIME, 'image/', 6) == 0) $file = media_get_from_URL($media, $EXT, $CACHE);
        if(!$file) {
            //download failed - redirect to original URL
            return array(302, $media);
        }
    } else {
        $media = cleanID($media);
        if(empty($media)) {
            return array(400, 'Bad request');
        }

        //check permissions (namespace only)
        if(auth_quickaclcheck(getNS($media).':X') < AUTH_READ) {
            return array(403, 'Forbidden');
        }
        $file = mediaFN($media, $rev);
    }

    //check file existance
    if(!@file_exists($file)) {
        return array(404, 'Not Found');
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
function calc_cache($cache) {
    global $conf;

    if(strtolower($cache) == 'nocache') return 0; //never cache
    if(strtolower($cache) == 'recache') return $conf['cachetime']; //use standard cache
    return -1; //cache endless
}

//Setup VIM: ex: et ts=2 :
