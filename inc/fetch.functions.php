<?php
/**
 * Functions used by lib/exe/fetch.php
 * (not included by other parts of dokuwiki)
 */

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
 * @author Gerry Weissbach <dokuwiki@gammaproduction.de>
 *
 * @param string $file   local file to send
 * @param string $mime   mime type of the file
 * @param bool   $dl     set to true to force a browser download
 * @param int    $cache  remaining cache time in seconds (-1 for $conf['cache'], 0 for no-cache)
 * @param bool   $public is this a public ressource or a private one?
 * @param string $orig   original file to send - the file name will be used for the Content-Disposition
 */
function sendFile($file, $mime, $dl, $cache, $public = false, $orig = null) {
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
        } else {
            // cache in browser
            header('Expires: '.gmdate("D, d M Y H:i:s", $expires).' GMT');
            header('Cache-Control: private, no-transform, max-age='.$maxage);
        }
    } else {
        // no cache at all
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Cache-Control: no-cache, no-transform');
    }

    //send important headers first, script stops here if '304 Not Modified' response
    $fmtime = @filemtime($file);
    http_conditionalRequest($fmtime);

    // Use the current $file if is $orig is not set.
    if ( $orig == null ) {
        $orig = $file;
    }

    //download or display?
    if($dl) {
        header('Content-Disposition: attachment;'.rfc2231_encode('filename', utf8_basename($orig)).';');
    } else {
        header('Content-Disposition: inline;'.rfc2231_encode('filename', utf8_basename($orig)).';');
    }

    //use x-sendfile header to pass the delivery to compatible webservers
    http_sendfile($file);

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
 * Try an rfc2231 compatible encoding. This ensures correct
 * interpretation of filenames outside of the ASCII set.
 * This seems to be needed for file names with e.g. umlauts that
 * would otherwise decode wrongly in IE.
 *
 * There is no additional checking, just the encoding and setting the key=value for usage in headers
 *
 * @author Gerry Weissbach <gerry.w@gammaproduction.de>
 * @param string $name      name of the field to be set in the header() call
 * @param string $value     value of the field to be set in the header() call
 * @param string $charset   used charset for the encoding of value
 * @param string $lang      language used.
 * @return string           in the format " name=value" for values WITHOUT special characters
 * @return string           in the format " name*=charset'lang'value" for values WITH special characters
 */
function rfc2231_encode($name, $value, $charset='utf-8', $lang='en') {
    $internal = preg_replace_callback('/[\x00-\x20*\'%()<>@,;:\\\\"\/[\]?=\x80-\xFF]/', function($match) { return rawurlencode($match[0]); }, $value);
    if ( $value != $internal ) {
        return ' '.$name.'*='.$charset."'".$lang."'".$internal;
    } else {
        return ' '.$name.'="'.$value.'"';
    }
}

/**
 * Check for media for preconditions and return correct status code
 *
 * READ: MEDIA, MIME, EXT, CACHE
 * WRITE: MEDIA, FILE, array( STATUS, STATUSMESSAGE )
 *
 * @author Gerry Weissbach <gerry.w@gammaproduction.de>
 *
 * @param string $media  reference to the media id
 * @param string $file   reference to the file variable
 * @param string $rev
 * @param int    $width
 * @param int    $height
 * @return array as array(STATUS, STATUSMESSAGE)
 */
function checkFileStatus(&$media, &$file, $rev = '', $width=0, $height=0) {
    global $MIME, $EXT, $CACHE, $INPUT;

    //media to local file
    if(media_isexternal($media)) {
        //check token for external image and additional for resized and cached images
        if(media_get_token($media, $width, $height) !== $INPUT->str('tok')) {
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
        // check token for resized images
        if (($width || $height) && media_get_token($media, $width, $height) !== $INPUT->str('tok')) {
            return array(412, 'Precondition Failed');
        }

        //check permissions (namespace only)
        if(auth_quickaclcheck(getNS($media).':X') < AUTH_READ) {
            return array(403, 'Forbidden');
        }
        $file = mediaFN($media, $rev);
    }

    //check file existance
    if(!file_exists($file)) {
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
 *
 * @param string $cache
 * @return int cachetime in seconds
 */
function calc_cache($cache) {
    global $conf;

    if(strtolower($cache) == 'nocache') return 0; //never cache
    if(strtolower($cache) == 'recache') return $conf['cachetime']; //use standard cache
    return -1; //cache endless
}
