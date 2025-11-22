<?php

/**
 * EasyWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use easywiki\TaskRunner;

if (!defined('WIKI_INC')) {
    define('WIKI_INC', __DIR__ . '/../../');
}
define('WIKI_DISABLE_GZIP_OUTPUT', 1);
require_once WIKI_INC . 'inc/init.php';
session_write_close();  //close session

$taskRunner = new TaskRunner();
$taskRunner->run();
