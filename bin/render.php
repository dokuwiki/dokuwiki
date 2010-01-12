#!/usr/bin/php
<?php
/**
 * A simple commandline tool to render some DokuWiki syntax with a given
 * renderer.
 *
 * This may not work for plugins that expect a certain environment to be
 * set up before rendering, but should work for most or even all standard
 * DokuWiki markup
 *
 * @license GPL2
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if ('cli' != php_sapi_name()) die();

ini_set('memory_limit','128M');
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
define('NOSESSION',1);
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/parserutils.php');
require_once(DOKU_INC.'inc/cliopts.php');

// handle options
$short_opts = 'hr:';
$long_opts  = array('help','renderer:');
$OPTS = Doku_Cli_Opts::getOptions(__FILE__,$short_opts,$long_opts);
if ( $OPTS->isError() ) {
    fwrite( STDERR, $OPTS->getMessage() . "\n");
    _usage();
    exit(1);
}
$RENDERER = 'xhtml';
foreach ($OPTS->options as $key => $val) {
    switch ($key) {
        case 'h':
        case 'help':
            _usage();
            exit;
        case 'r':
        case 'renderer':
            $RENDERER = $val;
    }
}


// do the action
$source = stream_get_contents(STDIN);
$info = array();
$result = p_render($RENDERER,p_get_instructions($source),$info);
if(is_null($result)) die("No such renderer $RENDERER\n");
echo $result;

/**
 * Print usage info
 */
function _usage(){
    print "Usage: render.php <options>

    Reads DokuWiki syntax from STDIN and renders it with the given renderer
    to STDOUT

    OPTIONS
        -h, --help                 show this help and exit
        -r, --renderer <renderer>  the render mode (default: xhtml)
";
}
