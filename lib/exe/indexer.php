<?php
/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Just send a 1x1 pixel blank gif to the browser and exit
  
 */
function sendGIF(){
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header('Content-Type: image/gif');
    header('Content-Length: '.strlen($img));
    header('Connection: Close');
    print $img;
    // Browser should drop connection after this
    // Thinks it's got the whole image
}

// Make sure image is sent to the browser immediately
ob_implicit_flush(TRUE);

// keep running after browser closes connection
@ignore_user_abort(true);

sendGIF();

// Switch off implicit flush again - we don't want to send any more output
ob_implicit_flush(FALSE);

// Catch any possible output (e.g. errors)
// - probably not needed but better safe...
ob_start();

// Called to exit - we don't want any output going anywhere
function indexer_stop() {
    ob_end_clean();
    exit();
}

// Now start work
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once(DOKU_INC.'inc/init.php');
//close session
session_write_close();

require_once(DOKU_INC.'inc/indexer.php');

$ID = cleanID($_REQUEST['id']);
if(!$ID) indexer_stop();

// check if indexing needed
$last = @filemtime(metaFN($ID,'.indexed'));
if($last > @filemtime(wikiFN($ID))) indexer_stop();

// try to aquire a lock
$lock = $conf['lockdir'].'/_indexer.lock';
while(!@mkdir($lock,0777)){
    if(time()-@filemtime($lock) > 60*5){
        // looks like a stale lock - remove it
        @rmdir($lock);
    }else{
        indexer_stop();
    }
}

// do the work
idx_addPage($ID);

// we're finished
io_saveFile(metaFN($ID,'.indexed'),'');
@rmdir($lock);
indexer_stop();

//Setup VIM: ex: et ts=4 enc=utf-8 :

// No trailing PHP closing tag - no output please!
// See Note at http://www.php.net/manual/en/language.basic-syntax.instruction-separation.php
