<?php

/**
 * EasyWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use easywiki\Utf8\Clean;
use easywiki\Ajax;

if (!defined('WIKI_INC')) define('WIKI_INC', __DIR__ . '/../../');
require_once(WIKI_INC . 'inc/init.php');

//close session
session_write_close();

// default header, ajax call may overwrite it later
header('Content-Type: text/html; charset=utf-8');

//call the requested function
global $INPUT;
if ($INPUT->has('call')) {
    $call = $INPUT->filter([Clean::class, 'stripspecials'])->str('call');
    new Ajax($call);
} else {
    http_status(404);
}
