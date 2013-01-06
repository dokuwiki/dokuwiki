#!/usr/bin/php
<?php
if ('cli' != php_sapi_name()) die();

ini_set('memory_limit','128M');
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_INC.'inc/init.php');
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
    $data = array();
    _quietecho("Searching pages... ");
    search($data,$conf['datadir'],'search_allpages',array('skipacl' => true));
    _quietecho(count($data)." pages found.\n");

    foreach($data as $val){
        _index($val['id']);
    }
}

function _index($id){
    global $CLEAR;
    global $QUIET;

    _quietecho("$id... ");
    idx_addPage($id, !$QUIET, $CLEAR);
    _quietecho("done.\n");
}

/**
 * Clear all index files
 */
function _clearindex(){
    _quietecho("Clearing index... ");
    idx_get_indexer()->clear();
    _quietecho("done.\n");
}

function _quietecho($msg) {
    global $QUIET;
    if(!$QUIET) echo $msg;
}

//Setup VIM: ex: et ts=2 :
