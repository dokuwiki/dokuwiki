<?php
/**
 * DokuWiki StyleSheet creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
if(!defined('NOSESSION')) define('NOSESSION',true); // we do not use a session or authentication here (better caching)
if(!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT',1); // we gzip ourself here
require_once(DOKU_INC.'inc/init.php');

// Main (don't run when UNIT test)
if(!defined('SIMPLE_TEST')){
    header('Content-Type: text/css; charset=utf-8');
    css_out();
}


// ---------------------- functions ------------------------------

/**
 * Output all needed Styles
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_out(){
    global $conf;
    global $lang;
    global $config_cascade;

    $style = '';
    if (isset($_REQUEST['s']) &&
        in_array($_REQUEST['s'], array('all', 'print', 'feed'))) {
        $style = $_REQUEST['s'];
    }

    $tpl = trim(preg_replace('/[^\w-]+/','',$_REQUEST['t']));
    if($tpl){
        $tplinc = DOKU_INC.'lib/tpl/'.$tpl.'/';
        $tpldir = DOKU_BASE.'lib/tpl/'.$tpl.'/';
    }else{
        $tplinc = DOKU_TPLINC;
        $tpldir = DOKU_TPL;
    }

    // The generated script depends on some dynamic options
    $cache = getCacheName('styles'.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'].DOKU_BASE.$tplinc.$style,'.css');

    // load template styles
    $tplstyles = array();
    if(@file_exists($tplinc.'style.ini')){
        $ini = parse_ini_file($tplinc.'style.ini',true);
        foreach($ini['stylesheets'] as $file => $mode){
            $tplstyles[$mode][$tplinc.$file] = $tpldir;
        }
    }

    // Array of needed files and their web locations, the latter ones
    // are needed to fix relative paths in the stylesheets
    $files   = array();
    //if (isset($tplstyles['all'])) $files = array_merge($files, $tplstyles['all']);
    if(!empty($style)){
        $files[DOKU_INC.'lib/styles/'.$style.'.css'] = DOKU_BASE.'lib/styles/';
        // load plugin, template, user styles
        $files = array_merge($files, css_pluginstyles($style));
        if (isset($tplstyles[$style])) $files = array_merge($files, $tplstyles[$style]);

        if(isset($config_cascade['userstyle'][$style])){
            $files[$config_cascade['userstyle'][$style]] = DOKU_BASE;
        }
    }else{
        $files[DOKU_INC.'lib/styles/style.css'] = DOKU_BASE.'lib/styles/';
        // load plugin, template, user styles
        $files = array_merge($files, css_pluginstyles('screen'));
        if (isset($tplstyles['screen'])) $files = array_merge($files, $tplstyles['screen']);
        if($lang['direction'] == 'rtl'){
            if (isset($tplstyles['rtl'])) $files = array_merge($files, $tplstyles['rtl']);
        }
        if(isset($config_cascade['userstyle']['default'])){
            $files[$config_cascade['userstyle']['default']] = DOKU_BASE;
        }
    }

    // check cache age & handle conditional request
    header('Cache-Control: public, max-age=3600');
    header('Pragma: public');
    if(css_cacheok($cache,array_keys($files),$tplinc)){
        http_conditionalRequest(filemtime($cache));
        if($conf['allowdebug']) header("X-CacheUsed: $cache");

        // finally send output
        if ($conf['gzip_output'] && http_gzip_valid($cache)) {
          header('Vary: Accept-Encoding');
          header('Content-Encoding: gzip');
          readfile($cache.".gz");
        } else {
          if (!http_sendfile($cache)) readfile($cache);
        }

        return;
    } else {
        http_conditionalRequest(time());
    }

    // start output buffering and build the stylesheet
    ob_start();

    // print the default classes for interwiki links and file downloads
    css_interwiki();
    css_filetypes();

    // load files
    foreach($files as $file => $location){
        print css_loadfile($file, $location);
    }

    // end output buffering and get contents
    $css = ob_get_contents();
    ob_end_clean();

    // apply style replacements
    $css = css_applystyle($css,$tplinc);

    // compress whitespace and comments
    if($conf['compress']){
        $css = css_compress($css);
    }

    // save cache file
    io_saveFile($cache,$css);
    if(function_exists('gzopen')) io_saveFile("$cache.gz",$css);

    // finally send output
    if ($conf['gzip_output']) {
      header('Vary: Accept-Encoding');
      header('Content-Encoding: gzip');
      print gzencode($css,9,FORCE_GZIP);
    } else {
      print $css;
    }
}

/**
 * Checks if a CSS Cache file still is valid
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_cacheok($cache,$files,$tplinc){
    global $config_cascade;

    if(isset($_REQUEST['purge'])) return false; //support purge request

    $ctime = @filemtime($cache);
    if(!$ctime) return false; //There is no cache

    // some additional files to check
    $files = array_merge($files, getConfigFiles('main'));
    $files[] = $tplinc.'style.ini';
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
 * Does placeholder replacements in the style according to
 * the ones defined in a templates style.ini file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_applystyle($css,$tplinc){
    if(@file_exists($tplinc.'style.ini')){
        $ini = parse_ini_file($tplinc.'style.ini',true);
        $css = strtr($css,$ini['replacements']);
    }
    return $css;
}

/**
 * Prints classes for interwikilinks
 *
 * Interwiki links have two classes: 'interwiki' and 'iw_$name>' where
 * $name is the identifier given in the config. All Interwiki links get
 * an default style with a default icon. If a special icon is available
 * for an interwiki URL it is set in it's own class. Both classes can be
 * overwritten in the template or userstyles.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_interwiki(){

    // default style
    echo 'a.interwiki {';
    echo ' background: transparent url('.DOKU_BASE.'lib/images/interwiki.png) 0px 1px no-repeat;';
    echo ' padding-left: 16px;';
    echo '}';

    // additional styles when icon available
    $iwlinks = getInterwiki();
    foreach(array_keys($iwlinks) as $iw){
        $class = preg_replace('/[^_\-a-z0-9]+/i','_',$iw);
        if(@file_exists(DOKU_INC.'lib/images/interwiki/'.$iw.'.png')){
            echo "a.iw_$class {";
            echo '  background-image: url('.DOKU_BASE.'lib/images/interwiki/'.$iw.'.png)';
            echo '}';
        }elseif(@file_exists(DOKU_INC.'lib/images/interwiki/'.$iw.'.gif')){
            echo "a.iw_$class {";
            echo '  background-image: url('.DOKU_BASE.'lib/images/interwiki/'.$iw.'.gif)';
            echo '}';
        }
    }
}

/**
 * Prints classes for file download links
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_filetypes(){

    // default style
    echo 'a.mediafile {';
    echo ' background: transparent url('.DOKU_BASE.'lib/images/fileicons/file.png) 0px 1px no-repeat;';
    echo ' padding-left: 18px;';
    echo ' padding-bottom: 1px;';
    echo '}';

    // additional styles when icon available
    // scan directory for all icons
    $exts = array();
    if($dh = opendir(DOKU_INC.'lib/images/fileicons')){
        while(false !== ($file = readdir($dh))){
            if(preg_match('/([_\-a-z0-9]+(?:\.[_\-a-z0-9]+)*?)\.(png|gif)/i',$file,$match)){
                $ext = strtolower($match[1]);
                $type = '.'.strtolower($match[2]);
                if($ext!='file' && (!isset($exts[$ext]) || $type=='.png')){
                    $exts[$ext] = $type;
                }
            }
        }
        closedir($dh);
    }
    foreach($exts as $ext=>$type){
        $class = preg_replace('/[^_\-a-z0-9]+/','_',$ext);
        echo "a.mf_$class {";
        echo '  background-image: url('.DOKU_BASE.'lib/images/fileicons/'.$ext.$type.')';
        echo '}';
    }
}

/**
 * Loads a given file and fixes relative URLs with the
 * given location prefix
 */
function css_loadfile($file,$location=''){
    if(!@file_exists($file)) return '';
    $css = io_readFile($file);
    if(!$location) return $css;

    $css = preg_replace('#(url\([ \'"]*)((?!/|http://|https://| |\'|"))#','\\1'.$location.'\\3',$css);
    return $css;
}


/**
 * Returns a list of possible Plugin Styles (no existance check here)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_pluginstyles($mode='screen'){
    global $lang;
    $list = array();
    $plugins = plugin_list();
    foreach ($plugins as $p){
        if($mode == 'all'){
            $list[DOKU_PLUGIN."$p/all.css"]  = DOKU_BASE."lib/plugins/$p/";
        }elseif($mode == 'print'){
            $list[DOKU_PLUGIN."$p/print.css"]  = DOKU_BASE."lib/plugins/$p/";
        }elseif($mode == 'feed'){
            $list[DOKU_PLUGIN."$p/feed.css"]  = DOKU_BASE."lib/plugins/$p/";
        }else{
            $list[DOKU_PLUGIN."$p/style.css"]  = DOKU_BASE."lib/plugins/$p/";
            $list[DOKU_PLUGIN."$p/screen.css"] = DOKU_BASE."lib/plugins/$p/";
        }
        if($lang['direction'] == 'rtl'){
            $list[DOKU_PLUGIN."$p/rtl.css"] = DOKU_BASE."lib/plugins/$p/";
        }
    }
    return $list;
}

/**
 * Very simple CSS optimizer
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_compress($css){
    //strip comments through a callback
    $css = preg_replace_callback('#(/\*)(.*?)(\*/)#s','css_comment_cb',$css);

    //strip (incorrect but common) one line comments
    $css = preg_replace('/(?<!:)\/\/.*$/m','',$css);

    // strip whitespaces
    $css = preg_replace('![\r\n\t ]+!',' ',$css);
    $css = preg_replace('/ ?([:;,{}\/]) ?/','\\1',$css);

    // shorten colors
    $css = preg_replace("/#([0-9a-fA-F]{1})\\1([0-9a-fA-F]{1})\\2([0-9a-fA-F]{1})\\3/", "#\\1\\2\\3",$css);

    return $css;
}

/**
 * Callback for css_compress()
 *
 * Keeps short comments (< 5 chars) to maintain typical browser hacks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_comment_cb($matches){
    if(strlen($matches[2]) > 4) return '';
    return $matches[0];
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
