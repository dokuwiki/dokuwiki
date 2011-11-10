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

    $mediatype = 'screen';
    if (isset($_REQUEST['s']) &&
        in_array($_REQUEST['s'], array('all', 'print', 'feed'))) {
        $mediatype = $_REQUEST['s'];
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
    $cache = new cache('styles'.$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'].DOKU_BASE.$tplinc.$mediatype,'.css');

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
    // load core styles
    $files[DOKU_INC.'lib/styles/'.$mediatype.'.css'] = DOKU_BASE.'lib/styles/';
    // load jQuery-UI theme
    $files[DOKU_INC.'lib/scripts/jquery/jquery-ui-theme/smoothness.css'] = DOKU_BASE.'lib/scripts/jquery/jquery-ui-theme/';
    // load plugin styles
    $files = array_merge($files, css_pluginstyles($mediatype));
    // load template styles
    if (isset($tplstyles[$mediatype])) {
        $files = array_merge($files, $tplstyles[$mediatype]);
    }
    // if old 'default' userstyle setting exists, make it 'screen' userstyle for backwards compatibility
    if (isset($config_cascade['userstyle']['default'])) {
        $config_cascade['userstyle']['screen'] = $config_cascade['userstyle']['default'];
    }
    // load user styles
    if(isset($config_cascade['userstyle'][$mediatype])){
        $files[$config_cascade['userstyle'][$mediatype]] = DOKU_BASE;
    }
    // load rtl styles
    // @todo: this currently adds the rtl styles only to the 'screen' media type
    //        but 'print' and 'all' should also be supported
    if ($mediatype=='screen') {
        if($lang['direction'] == 'rtl'){
            if (isset($tplstyles['rtl'])) $files = array_merge($files, $tplstyles['rtl']);
        }
    }

    $cache_files = array_merge(array_keys($files), getConfigFiles('main'));
    $cache_files[] = $tplinc.'style.ini';
    $cache_files[] = __FILE__;

    // check cache age & handle conditional request
    // This may exit if a cache can be used
    http_cached($cache->cache,
                $cache->useCache(array('files' => $cache_files)));

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

    // place all @import statements at the top of the file
    $css = css_moveimports($css);

    // compress whitespace and comments
    if($conf['compress']){
        $css = css_compress($css);
    }

    // embed small images right into the stylesheet
    if($conf['cssdatauri']){
        $base = preg_quote(DOKU_BASE,'#');
        $css = preg_replace_callback('#(url\([ \'"]*)('.$base.')(.*?(?:\.(png|gif)))#i','css_datauri',$css);
    }

    http_cached_finish($cache->cache, $css);
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
    echo ' padding: 1px 0px 1px 16px;';
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
    echo '.mediafile {';
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
        echo ".mf_$class {";
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

    $css = preg_replace('#(url\([ \'"]*)(?!/|data:|http://|https://| |\'|")#','\\1'.$location,$css);
    $css = preg_replace('#(@import\s+[\'"])(?!/|data:|http://|https://)#', '\\1'.$location, $css);

    return $css;
}

/**
 * Converte local image URLs to data URLs if the filesize is small
 *
 * Callback for preg_replace_callback
 */
function css_datauri($match){
    global $conf;

    $pre   = unslash($match[1]);
    $base  = unslash($match[2]);
    $url   = unslash($match[3]);
    $ext   = unslash($match[4]);

    $local = DOKU_INC.$url;
    $size  = @filesize($local);
    if($size && $size < $conf['cssdatauri']){
        $data = base64_encode(file_get_contents($local));
    }
    if($data){
        $url = 'data:image/'.$ext.';base64,'.$data;
    }else{
        $url = $base.$url;
    }
    return $pre.$url;
}


/**
 * Returns a list of possible Plugin Styles (no existance check here)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_pluginstyles($mediatype='screen'){
    global $lang;
    $list = array();
    $plugins = plugin_list();
    foreach ($plugins as $p){
        $list[DOKU_PLUGIN."$p/$mediatype.css"]  = DOKU_BASE."lib/plugins/$p/";
        // alternative for screen.css
        if ($mediatype=='screen') {
            $list[DOKU_PLUGIN."$p/style.css"]  = DOKU_BASE."lib/plugins/$p/";
        }
        if($lang['direction'] == 'rtl'){
            $list[DOKU_PLUGIN."$p/rtl.css"] = DOKU_BASE."lib/plugins/$p/";
        }
    }
    return $list;
}

/**
 * Move all @import statements in a combined stylesheet to the top so they
 * aren't ignored by the browser.
 *
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function css_moveimports($css)
{
    if(!preg_match_all('/@import\s+(?:url\([^)]+\)|"[^"]+")\s*[^;]*;\s*/', $css, $matches, PREG_OFFSET_CAPTURE)) {
        return $css;
    }
    $newCss  = "";
    $imports = "";
    $offset  = 0;
    foreach($matches[0] as $match) {
        $newCss  .= substr($css, $offset, $match[1] - $offset);
        $imports .= $match[0];
        $offset   = $match[1] + strlen($match[0]);
    }
    $newCss .= substr($css, $offset);
    return $imports.$newCss;
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
    $css = preg_replace('/ ?([;,{}\/]) ?/','\\1',$css);
    $css = preg_replace('/ ?: /',':',$css);

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

//Setup VIM: ex: et ts=4 :
