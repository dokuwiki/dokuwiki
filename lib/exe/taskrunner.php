<?php

/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\TaskRunner;

if (!defined('DOKU_INC')) {
    define('DOKU_INC', __DIR__ . '/../../');
}
define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once DOKU_INC . 'inc/init.php';
session_write_close();  //close session

$taskRunner = new TaskRunner();
$taskRunner->run();
