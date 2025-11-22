<?php

/**
 * EasyWiki OpenSearch creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.opensearch.org/
 * @author     Mike Frysinger <vapier@gentoo.org>
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if (!defined('WIKI_INC')) define('WIKI_INC', __DIR__ . '/../../');
if (!defined('NOSESSION')) define('NOSESSION', true); // we do not use a session or authentication here (better caching)
if (!defined('NL')) define('NL', "\n");
require_once(WIKI_INC . 'inc/init.php');

// try to be clever about the favicon location
if (file_exists(WIKI_INC . 'favicon.ico')) {
    $ico = WIKI_URL . 'favicon.ico';
} elseif (file_exists(tpl_incdir() . 'images/favicon.ico')) {
    $ico = WIKI_URL . 'lib/tpl/' . $conf['template'] . '/images/favicon.ico';
} elseif (file_exists(tpl_incdir() . 'favicon.ico')) {
    $ico = WIKI_URL . 'lib/tpl/' . $conf['template'] . '/favicon.ico';
} else {
    $ico = WIKI_URL . 'lib/tpl/easywiki/images/favicon.ico';
}

// output
header('Content-Type: application/opensearchdescription+xml; charset=utf-8');
echo '<?xml version="1.0"?>' . NL;
echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">' . NL;
echo '  <ShortName>' . hsc($conf['title']) . '</ShortName>' . NL;
echo '  <Image width="16" height="16" type="image/x-icon">' . $ico . '</Image>' . NL;
echo '  <Url type="text/html" template="' . WIKI_URL . WIKI_SCRIPT . '?do=search&amp;id={searchTerms}" />' . NL;
echo '  <Url type="application/x-suggestions+json" template="' .
    WIKI_URL . 'lib/exe/ajax.php?call=suggestions&amp;q={searchTerms}" />' . NL;
echo '</OpenSearchDescription>' . NL;
