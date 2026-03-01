<?php

/**
 * DokuWiki media passthrough file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Input\Input;
use dokuwiki\Extension\Event;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC . 'inc/init.php');
session_write_close(); //close session

require_once(DOKU_INC . 'inc/fetch.functions.php');

if (defined('SIMPLE_TEST')) {
    $INPUT = new Input();
}

// BEGIN main
$mimetypes = getMimeTypes();

//get input
$MEDIA = stripctl(getID('media', false)); // no cleaning except control chars - maybe external
$CACHE = calc_cache($INPUT->str('cache'));
$WIDTH = $INPUT->int('w');
$HEIGHT = $INPUT->int('h');
$REV = &$INPUT->ref('rev');
//sanitize revision
$REV = preg_replace('/[^0-9]/', '', $REV);

[$EXT, $MIME, $DL] = mimetype($MEDIA, false);
if ($EXT === false) {
    $EXT = 'unknown';
    $MIME = 'application/octet-stream';
    $DL = true;
}

// check for permissions, preconditions and cache external files
[$STATUS, $STATUSMESSAGE] = checkFileStatus($MEDIA, $FILE, $REV, $WIDTH, $HEIGHT);

// prepare data for plugin events
$data = [
    'media' => $MEDIA,
    'file' => $FILE,
    'orig' => $FILE,
    'mime' => $MIME,
    'download' => $DL,
    'cache' => $CACHE,
    'ext' => $EXT,
    'width' => $WIDTH,
    'height' => $HEIGHT,
    'status' => $STATUS,
    'statusmessage' => $STATUSMESSAGE,
    'ispublic' => media_ispublic($MEDIA),
    'csp' => [
        'default-src' => "'none'",
        'style-src' => "'unsafe-inline'",
        'media-src' => "'self'",
        'object-src' => "'self'",
        'font-src' => "'self' data:",
        'form-action' => "'none'",
        'frame-ancestors' => "'self'",
    ]
];

// handle the file status
$evt = new Event('FETCH_MEDIA_STATUS', $data);
if ($evt->advise_before()) {
    // redirects
    if ($data['status'] > 300 && $data['status'] <= 304) {
        if (defined('SIMPLE_TEST')) return; //TestResponse doesn't recognize redirects
        send_redirect($data['statusmessage']);
    }
    // send any non 200 status
    if ($data['status'] != 200) {
        http_status($data['status'], $data['statusmessage']);
    }
    // die on errors
    if ($data['status'] > 203) {
        echo $data['statusmessage'];
        if (defined('SIMPLE_TEST')) return;
        exit;
    }
}
$evt->advise_after();
unset($evt);

//handle image resizing/cropping
$evt = new Event('MEDIA_RESIZE', $data);
if ($evt->advise_before()) {
    if (
        $MIME != 'image/svg+xml' &&
        str_starts_with($MIME, 'image') &&
        ($WIDTH || $HEIGHT)
    ) {
        if ($HEIGHT && $WIDTH) {
            $data['file'] = $FILE = media_crop_image($data['file'], $EXT, $WIDTH, $HEIGHT);
        } else {
            $data['file'] = $FILE = media_resize_image($data['file'], $EXT, $WIDTH, $HEIGHT);
        }
    }
}
$evt->advise_after();
unset($evt);

// finally send the file to the client
$evt = new Event('MEDIA_SENDFILE', $data);
if ($evt->advise_before()) {
    sendFile(
        $data['file'],
        $data['mime'],
        $data['download'],
        $data['cache'],
        $data['ispublic'],
        $data['orig'],
        $data['csp']
    );
}
// Do something after the download finished.
$evt->advise_after();  // will not be emitted on 304 or x-sendfile

// END DO main
