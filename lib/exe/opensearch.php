<?php
/**
 * DokuWiki OpenSearch creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.opensearch.org/
 * @author     Mike Frysinger <vapier@gentoo.org>
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
if(!defined('NOSESSION')) define('NOSESSION',true); // we do not use a session or authentication here (better caching)
if(!defined('NL')) define('NL',"\n");
require_once(DOKU_INC.'inc/init.php');

// try to be clever about the favicon location
if(file_exists(DOKU_INC.'favicon.ico')){
    $ico = DOKU_URL.'favicon.ico';
}elseif(file_exists(DOKU_TPLINC.'images/favicon.ico')){
    $ico = DOKU_URL.'lib/tpl/'.$conf['template'].'/images/favicon.ico';
}elseif(file_exists(DOKU_TPLINC.'favicon.ico')){
    $ico = DOKU_URL.'lib/tpl/'.$conf['template'].'/favicon.ico';
}else{
    $ico = DOKU_URL.'lib/tpl/default/images/favicon.ico';
}

// output
header('Content-Type: application/opensearchdescription+xml; charset=utf-8');
echo '<?xml version="1.0"?>'.NL;
echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">'.NL;
echo '  <ShortName>'.htmlspecialchars($conf['title']).'</ShortName>'.NL;
echo '  <Image width="16" height="16" type="image/x-icon">'.$ico.'</Image>'.NL;
echo '  <Url type="text/html" template="'.DOKU_URL.DOKU_SCRIPT.'?do=search&amp;id={searchTerms}" />'.NL;
echo '  <Url type="application/x-suggestions+json" template="'.
        DOKU_URL.'lib/exe/ajax.php?call=suggestions&amp;q={searchTerms}" />'.NL;
echo '</OpenSearchDescription>'.NL;

//Setup VIM: ex: et ts=4 enc=utf-8 :
