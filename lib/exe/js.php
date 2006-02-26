<?php
/**
 * DokuWiki JavaScript creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('NOSESSION')) define('NOSESSION',true); // we do not use a session or authentication here (better caching)
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/io.php');

// Main (don't run when UNIT test)
if(!defined('SIMPLE_TEST')){
    header('Content-Type: text/javascript; charset=utf-8');
    js_out();
}


// ---------------------- functions ------------------------------

/**
 * Output all needed JavaScript
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_out(){
    global $conf;
    global $lang;
    $edit  = (bool) $_REQUEST['edit'];   // edit or preview mode?
    $write = (bool) $_REQUEST['write'];  // writable?

    // The generated script depends on some dynamic options
    $cache = getCacheName('scripts'.$edit.$write,'.js'); 

    // Array of needed files
    $files = array(
                DOKU_INC.'lib/scripts/events.js',
                DOKU_INC.'lib/scripts/script.js',
                DOKU_INC.'lib/scripts/tw-sack.js',
                DOKU_INC.'lib/scripts/ajax.js',
                DOKU_INC.'lib/scripts/domLib.js',
                DOKU_INC.'lib/scripts/domTT.js',
             );
    if($edit && $write){
        $files[] = DOKU_INC.'lib/scripts/edit.js';
        if($conf['spellchecker']){
            $files[] = DOKU_INC.'lib/scripts/spellcheck.js';
        }
    }
    $files[] = DOKU_TPLINC.'script.js';

    // get possible plugin scripts
    $plugins = js_pluginscripts();

    // check cache age here
    if(js_cacheok($cache,array_merge($files,$plugins))){
        readfile($cache);
        return;
    }

    // start output buffering and build the script
    ob_start();

    // add some translation strings and global variables
    print "var alertText   = '".js_escape($lang['qb_alert'])."';";
    print "var notSavedYet = '".js_escape($lang['notsavedyet'])."';";
    print "var reallyDel   = '".js_escape($lang['del_confirm'])."';";
    print "var DOKU_BASE   = '".DOKU_BASE."';";

    // load files
    foreach($files as $file){
        @readfile($file);
    }

    // init stuff
    js_runonstart("ajax_qsearch.init('qsearch__in','qsearch__out')");
    js_runonstart("addEvent(document,'click',closePopups)");
    js_runonstart('addTocToggle()');

    if($edit){
        // size controls
        js_runonstart("initSizeCtl('size__ctl','wiki__text')");

        if($write){
            require_once(DOKU_INC.'inc/toolbar.php');
            toolbar_JSdefines('toolbar');
            js_runonstart("initToolbar('tool__bar','wiki__text',toolbar)");

            // add pageleave check
            js_runonstart("initChangeCheck('".js_escape($lang['notsavedyet'])."')");

            // add lock timer
            js_runonstart("locktimer.init(".($conf['locktime'] - 60).",'".js_escape($lang['willexpire'])."')");

            // load spell checker
            if($conf['spellchecker']){
                js_runonstart("ajax_spell.init('".
                               js_escape($lang['spell_start'])."','".
                               js_escape($lang['spell_stop'])."','".
                               js_escape($lang['spell_wait'])."','".
                               js_escape($lang['spell_noerr'])."','".
                               js_escape($lang['spell_nosug'])."','".
                               js_escape($lang['spell_change'])."')");
            }
        }
    }

    // load plugin scripts (suppress warnings for missing ones)
    foreach($plugins as $plugin){
        @readfile($plugin);
    }

    // load user script
    @readfile(DOKU_CONF.'userscript.js');

    // add scroll event
    js_runonstart('scrollToMarker()');

    // initialize init pseudo event
    echo 'if (document.addEventListener) {';
    echo '    document.addEventListener("DOMContentLoaded", window.fireoninit, null);';
    echo '}';
    echo 'addEvent(window,"load",window.fireoninit);';

    // end output buffering and get contents
    $js = ob_get_contents();
    ob_end_clean();

    // compress whitespace and comments
    if($conf['compress']){
        $js = js_compress($js);
    }

    // save cache file
    io_saveFile($cache,$js);

    // finally send output
    print $js;
}

/**
 * Checks if a JavaScript Cache file still is valid
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_cacheok($cache,$files){
    $ctime = @filemtime($cache);
    if(!$ctime) return false; //There is no cache

    // some additional files to check
    $files[] = DOKU_CONF.'dokuwiki.php';
    $files[] = DOKU_CONF.'local.php';
    $files[] = DOKU_CONF.'userscript.js';
    $files[] = __FILE__;

    // now walk the files
    foreach($files as $file){
        if(@filemtime($file) > $ctime){
            return false;
        }
    }
    return true;
}

/**
 * Returns a list of possible Plugin Scripts (no existance check here)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_pluginscripts(){
    $list = array();
    $plugins = plugin_list();
    foreach ($plugins as $p){
        $list[] = DOKU_PLUGIN."$p/script.js";
    }
    return $list;
}

/**
 * Escapes a String to be embedded in a JavaScript call, keeps \n
 * as newline
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_escape($string){
    return str_replace('\\\\n','\\n',addslashes($string));
}

/**
 * Adds the given JavaScript code to the window.onload() event
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_runonstart($func){
    echo "addInitEvent(function(){ $func; });";
}

/**
 * Strip comments and whitespaces from given JavaScript Code
 *
 * This is a rewrite of Nick Galbreaths python tool jsstrip.py which is
 * released under BSD license. See link for original code.
 *
 * @author Nick Galbreath <nickg@modp.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link http://modp.com/release/jsstrip/
 */
function js_compress($s){
    $i = 0;
    $line = 0;
    $s .= "\n";
    $len = strlen($s);

    // items that don't need spaces next to them
    $chars = '^&|!+\-*\/%=:;,{}()<>% \t\n\r';

    ob_start();
    while($i < $len){
        $ch = $s{$i};

        // multiline comments
        if($ch == '/' && $s{$i+1} == '*'){
            $endC = strpos($s,'*/',$i+2);
            if($endC === false) trigger_error('Found invalid /*..*/ comment', E_USER_ERROR);
            $i = $endC + 2;
            continue;
        }

        // singleline
        if($ch == '/' && $s{$i+1} == '/'){
            $endC = strpos($s,"\n",$i+2);
            if($endC === false) trigger_error('Invalid comment', E_USER_ERROR);
            $i = $endC;
            continue;
        }

        // tricky.  might be an RE
        if($ch == '/'){
            // rewind, skip white space
            $j = 1;
            while($s{$i-$j} == ' '){
                $j = $j + 1;
            }
            if( ($s{$i-$j} == '=') || ($s{$i-$j} == '(') ){
                // yes, this is an re
                // now move forward and find the end of it
                $j = 1;
                while($s{$i+$j} != '/'){
                    while( ($s{$i+$j} != '\\') && ($s{$i+$j} != '/')){
                        $j = $j + 1;
                    }
                    if($s{$i+$j} == '\\') $j = $j + 2;
                }
                echo substr($s,$i,$j+1);
                $i = $i + $j + 1;
                continue;
            }
        }

        // double quote strings
        if($ch == '"'){
            $j = 1;
            while( $s{$i+$j} != '"' ){
                while( ($s{$i+$j} != '\\') && ($s{$i+$j} != '"') ){
                    $j = $j + 1;
                }
                if($s{$i+$j} == '\\') $j = $j + 2;
            }
            echo substr($s,$i,$j+1);
            $i = $i + $j + 1;
            continue;
        }

        // single quote strings
        if($ch == "'"){
            $j = 1;
            while( $s{$i+$j} != "'" ){
                while( ($s{$i+$j} != '\\') && ($s{$i+$j} != "'") ){
                    $j = $j + 1;
                }
                if ($s{$i+$j} == '\\') $j = $j + 2;
            }
            echo substr($s,$i,$j+1);
            $i = $i + $j + 1;
            continue;
        }

        // newlines
        if($ch == "\n" || $ch == "\r"){
            $i = $i+1;
            continue;
        }

        // leading spaces
        if( ( $ch == ' ' ||
              $ch == "\n" ||
              $ch == "\t" ) &&
            !preg_match('/['.$chars.']/',$s{$i+1}) ){
            $i = $i+1;
            continue;
        }

        // trailing spaces
        if( ( $ch == ' ' ||
              $ch == "\n" ||
              $ch == "\t" ) &&
            !preg_match('/['.$chars.']/',$s{$i-1}) ){
            $i = $i+1;
            continue;
        }

        // other chars
        echo $ch;
        $i = $i + 1;
    }


    $out = ob_get_contents();
    ob_end_clean();
    return $out;
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
