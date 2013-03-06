#!/usr/bin/php
<?php
/**
 * Strip unwanted languages from the DokuWiki install
 *
 * @author Martin 'E.T.' Misuth <et.github@ethome.sk>
 */
if ('cli' != php_sapi_name()) die();

#------------------------------------------------------------------------------
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once DOKU_INC.'inc/cliopts.php';

#------------------------------------------------------------------------------
function usage($show_examples = false) {
    print "Usage: striplangs.php [-h [-x]] [-e] [-k lang1[,lang2]..[,langN]]

    Removes all languages from the instalation, besides the ones
    after the -k option. English language is never removed!

    OPTIONS
        -h, --help     get this help
        -x, --examples get also usage examples
        -k, --keep     comma separated list of languages, -e is always implied
        -e, --english  keeps english, dummy to use without -k\n";
    if ( $show_examples ) {
        print "\n
    EXAMPLES
        Strips all languages, but keeps 'en' and 'de':
         striplangs -k de

        Strips all but 'en','ca-valencia','cs','de','is','sk':
         striplangs --keep ca-valencia,cs,de,is,sk

        Strips all but 'en':
         striplangs -e

        No option specified, prints usage and throws error:
         striplangs\n";
    }
}

function getSuppliedArgument($OPTS, $short, $long) {
    $arg = $OPTS->get($short);
    if ( is_null($arg) ) {
        $arg = $OPTS->get($long);
    }
    return $arg;
}

function processPlugins($path, $keep_langs) {
    if (is_dir($path)) {
        $entries = scandir($path);

        foreach ($entries as $entry) {
            if ($entry != "." && $entry != "..") {
                if ( is_dir($path.'/'.$entry) ) {

                    $plugin_langs = $path.'/'.$entry.'/lang';

                    if ( is_dir( $plugin_langs ) ) {
                        stripDirLangs($plugin_langs, $keep_langs);
                    }
                }
            }
        }
    }
}

function stripDirLangs($path, $keep_langs) {
    $dir = dir($path);

    while(($cur_dir = $dir->read()) !== false) {
        if( $cur_dir != '.' and $cur_dir != '..' and is_dir($path.'/'.$cur_dir)) {

            if ( !in_array($cur_dir, $keep_langs, true ) ) {
                killDir($path.'/'.$cur_dir);
            }
        }
    }
    $dir->close();
}

function killDir($dir) {
    if (is_dir($dir)) {
        $entries = scandir($dir);

        foreach ($entries as $entry) {
            if ($entry != "." && $entry != "..") {
                if ( is_dir($dir.'/'.$entry) ) {
                    killDir($dir.'/'.$entry);
                } else {
                    unlink($dir.'/'.$entry);
                }
            }
        }
        reset($entries);
        rmdir($dir);
    }
}
#------------------------------------------------------------------------------

// handle options
$short_opts = 'hxk:e';
$long_opts  = array('help', 'examples', 'keep=','english');

$OPTS = Doku_Cli_Opts::getOptions(__FILE__, $short_opts, $long_opts);

if ( $OPTS->isError() ) {
    fwrite( STDERR, $OPTS->getMessage() . "\n");
    exit(1);
}

// handle '--examples' option
$show_examples = ( $OPTS->has('x') or $OPTS->has('examples') ) ? true : false;

// handle '--help' option
if ( $OPTS->has('h') or $OPTS->has('help') ) {
    usage($show_examples);
    exit(0);
}

// handle both '--keep' and '--english' options
if ( $OPTS->has('k') or $OPTS->has('keep') ) {
    $preserved_langs = getSuppliedArgument($OPTS,'k','keep');
    $langs = explode(',', $preserved_langs);

    // ! always enforce 'en' lang when using '--keep' (DW relies on it)
    if ( !isset($langs['en']) ) {
      $langs[]='en';
    }
} elseif ( $OPTS->has('e') or $OPTS->has('english') ) {
    // '--english' was specified strip everything besides 'en'
    $langs = array ('en');
} else {
    // no option was specified, print usage but don't do anything as
    // this run might not be intented
    usage();
    print "\n
    ERROR
        No option specified, use either -h -x to get more info,
        or -e to strip every language besides english.\n";
    exit(1);
}

// Kill all language directories in /inc/lang and /lib/plugins besides those in $langs array
stripDirLangs(realpath(dirname(__FILE__).'/../inc/lang'), $langs);
processPlugins(realpath(dirname(__FILE__).'/../lib/plugins'), $langs);
