#!/usr/bin/php
<?php
if ('cli' != php_sapi_name()) die();

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/indexer.php');
require_once(DOKU_INC.'inc/cliopts.php');
session_write_close();

// handle options
$short_opts = 'hcu';
$long_opts  = array('help', 'clean', 'update');
$OPTS = Doku_Cli_Opts::getOptions(__FILE__,$short_opts,$long_opts);
if ( $OPTS->isError() ) {
    fwrite( STDERR, $OPTS->getMessage() . "\n");
    _usage();
    exit(1);
}
$CLEAR = false;
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
";
}

function _update(){
    global $conf;
    $data = array();
    echo "Searching pages... ";
    search($data,$conf['datadir'],'search_allpages',array());
    echo count($data)." pages found.\n";

    foreach($data as $val){
        _index($val['id']);
    }
}

function _index($id){
    global $CLEAR;

    // if not cleared only update changed and new files
    if(!$CLEAR){
      $last = @filemtime(metaFN($id,'.indexed'));
      if($last > @filemtime(wikiFN($id))) return;
    }

    _lock();
    echo "$id... ";
    idx_addPage($id);
    io_saveFile(metaFN($id,'.indexed'),' ');
    echo "done.\n";
    _unlock();
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
                echo ".";
            }else{
                echo "Waiting for lockfile (max. 5 min)";
                $said = true;
            }
            sleep(15);
        }
    }
    if($conf['dperm']) chmod($lock, $conf['dperm']);
    if($said) print "\n";
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
    echo "Clearing index... ";
    io_saveFile($conf['cachedir'].'/word.idx','');
    io_saveFile($conf['cachedir'].'/page.idx','');
    io_saveFile($conf['cachedir'].'/index.idx','');
    echo "done.\n";
    _unlock();
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
