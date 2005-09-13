<?php
/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/indexer.php');
//close session
session_write_close();


$ID = cleanID($_REQUEST['id']);
if(!$ID) sendGIF();

// check if indexing needed
$last = @filemtime(metaFN($ID,'.indexed'));
if($last > @filemtime(wikiFN($ID))) sendGIF();

// keep running
@ignore_user_abort(true);

// try to aquire a lock
$lock = $conf['lockdir'].'/_indexer.lock';
while(!@mkdir($lock)){
    if(time()-@filemtime($lock) > 60*5){
        // looks like a stale lock - remove it
        @rmdir($lock);
    }else{
        sendGIF();
    }
}

// do the work
idx_addPage($ID);

// we're finished
io_saveFile(metaFN($ID,'.indexed'),'');
@rmdir($lock);
sendGIF();

/**
 * Just send a 1x1 pixel blank gif to the browser and exit
 */
function sendGIF(){
    header('Content-Type: image/gif');
    print base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    exit;
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
