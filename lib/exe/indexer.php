<?php
/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
define('DOKU_DISABLE_GZIP_OUTPUT',1);
require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session
if(!defined('NL')) define('NL',"\n");

// keep running after browser closes connection
@ignore_user_abort(true);

// check if user abort worked, if yes send output early
$defer = !@ignore_user_abort() || $conf['broken_iua'];
$output = $INPUT->has('debug') && $conf['allowdebug'];
if(!$defer && !$output){
    sendGIF(); // send gif
}

$ID = cleanID($INPUT->str('id'));

// Catch any possible output (e.g. errors)
if(!$output) ob_start();
else header('Content-Type: text/plain');

$taskRunner = new \dokuwiki\TaskRunner();
$taskRunner->run();

if(!$output) {
    ob_end_clean();
    if($defer) sendGIF();
}

exit;

// --------------------------------------------------------------------

/**
 * Just send a 1x1 pixel blank gif to the browser
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <fuecks@gmail.com>
 */
function sendGIF(){
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header('Content-Type: image/gif');
    header('Content-Length: '.strlen($img));
    header('Connection: Close');
    print $img;
    tpl_flush();
    // Browser should drop connection after this
    // Thinks it's got the whole image
}

//Setup VIM: ex: et ts=4 :
// No trailing PHP closing tag - no output please!
// See Note at http://php.net/manual/en/language.basic-syntax.instruction-separation.php
