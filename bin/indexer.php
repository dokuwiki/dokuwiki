#!/usr/bin/php
<?php
if ('cli' != php_sapi_name()) die();

ini_set('memory_limit','128M');
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/indexer.php');
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'inc/cliopts.php');
session_write_close();

// handle options
$short_opts = 'hcuq';
$long_opts  = array('help', 'clear', 'update', 'quiet');
$OPTS = Doku_Cli_Opts::getOptions(__FILE__,$short_opts,$long_opts);
if ( $OPTS->isError() ) {
    fwrite( STDERR, $OPTS->getMessage() . "\n");
    _usage();
    exit(1);
}
$CLEAR = false;
$QUIET = false;
$INDEXER = null;
foreach ($OPTS->options as $key => $val) {
    switch ($key) {
        case 'h':
        case 'help':
            _usage();
            exit;
        case 'c':
        case 'clear':
            $CLEAR = true;
            break;
        case 'q':
        case 'quiet':
            $QUIET = true;
            break;
    }
}

#------------------------------------------------------------------------------
# Action

if($CLEAR) _clearindex();
_update();



#------------------------------------------------------------------------------

function _usage() {
    print "Usage: indexer.php <options>

    Updates the searchindex by indexing all new or changed pages
    when the -c option is given the index is cleared first.

    OPTIONS
        -h, --help     show this help and exit
        -c, --clear    clear the index before updating
        -q, --quiet    don't produce any output
";
}

function _update(){
    global $conf;
    global $INDEXER;

    $INDEXER = idx_get_indexer();

    $data = array();
    _quietecho("Searching pages... ");
    search($data,$conf['datadir'],'search_allpages',array('skipacl' => true));
    _quietecho(count($data)." pages found.\n");

    foreach($data as $val){
        _index($val['id']);
    }
}

function _index($id){
    global $INDEXER;
    global $CLEAR;
    global $QUIET;

    _quietecho("$id... ");
    idx_addPage($id, !$QUIET, $CLEAR);
    _quietecho("done.\n");
}

/**
 * lock the indexer system
 */
function _lock(){
    global $conf;
    $lock = $conf['lockdir'].'/_indexer.lock';
    $said = false;
    while(!@mkdir($lock, $conf['dmode'])){
        if(time()-@filemtime($lock) > 60*5){
            // looks like a stale lock - remove it
            @rmdir($lock);
        }else{
            if($said){
                _quietecho(".");
            }else{
                _quietecho("Waiting for lockfile (max. 5 min)");
                $said = true;
            }
            sleep(15);
        }
    }
    if($conf['dperm']) chmod($lock, $conf['dperm']);
    if($said) _quietecho("\n");
}

/**
 * unlock the indexer sytem
 */
function _unlock(){
    global $conf;
    $lock = $conf['lockdir'].'/_indexer.lock';
    @rmdir($lock);
}

/**
 * Clear all index files
 */
function _clearindex(){
    global $conf;
    _lock();
    _quietecho("Clearing index... ");
    io_saveFile($conf['indexdir'].'/page.idx','');
    io_saveFile($conf['indexdir'].'/title.idx','');
    io_saveFile($conf['indexdir'].'/pageword.idx','');
    io_saveFile($conf['indexdir'].'/metadata.idx','');
    $dir = @opendir($conf['indexdir']);
    if($dir!==false){
        while(($f = readdir($dir)) !== false){
            if(substr($f,-4)=='.idx' &&
               (substr($f,0,1)=='i' || substr($f,0,1)=='w'
               || substr($f,-6)=='_w.idx' || substr($f,-6)=='_i.idx' || substr($f,-6)=='_p.idx'))
                @unlink($conf['indexdir']."/$f");
        }
    }
    @unlink($conf['indexdir'].'/lengths.idx');
    _quietecho("done.\n");
    _unlock();
}

function _quietecho($msg) {
    global $QUIET;
    if(!$QUIET) echo $msg;
}

//Setup VIM: ex: et ts=2 :
