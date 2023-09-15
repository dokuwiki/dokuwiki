<?php

/**
 * DokuWiki JavaScript creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Utf8\PhpString;
use dokuwiki\Cache\Cache;
use dokuwiki\Extension\Event;
use splitbrain\JSStrip\Exception as JSStripException;
use splitbrain\JSStrip\JSStrip;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('NOSESSION')) define('NOSESSION', true); // we do not use a session or authentication here (better caching)
if (!defined('NL')) define('NL', "\n");
if (!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT', 1); // we gzip ourself here
require_once(DOKU_INC . 'inc/init.php');

// Main (don't run when UNIT test)
if (!defined('SIMPLE_TEST')) {
    header('Content-Type: application/javascript; charset=utf-8');
    js_out();
}


// ---------------------- functions ------------------------------

/**
 * Output all needed JavaScript
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_out()
{
    global $conf;
    global $lang;
    global $config_cascade;
    global $INPUT;

    // decide from where to get the template
    $tpl = trim(preg_replace('/[^\w-]+/', '', $INPUT->str('t')));
    if (!$tpl) $tpl = $conf['template'];

    // array of core files
    $files = [
        DOKU_INC . 'lib/scripts/jquery/jquery.cookie.js',
        DOKU_INC . 'inc/lang/' . $conf['lang'] . '/jquery.ui.datepicker.js',
        DOKU_INC . "lib/scripts/fileuploader.js",
        DOKU_INC . "lib/scripts/fileuploaderextended.js",
        DOKU_INC . 'lib/scripts/helpers.js',
        DOKU_INC . 'lib/scripts/delay.js',
        DOKU_INC . 'lib/scripts/cookie.js',
        DOKU_INC . 'lib/scripts/script.js',
        DOKU_INC . 'lib/scripts/qsearch.js',
        DOKU_INC . 'lib/scripts/search.js',
        DOKU_INC . 'lib/scripts/tree.js',
        DOKU_INC . 'lib/scripts/index.js',
        DOKU_INC . 'lib/scripts/textselection.js',
        DOKU_INC . 'lib/scripts/toolbar.js',
        DOKU_INC . 'lib/scripts/edit.js',
        DOKU_INC . 'lib/scripts/editor.js',
        DOKU_INC . 'lib/scripts/locktimer.js',
        DOKU_INC . 'lib/scripts/linkwiz.js',
        DOKU_INC . 'lib/scripts/media.js',
        DOKU_INC . 'lib/scripts/compatibility.js',
        # disabled for FS#1958                DOKU_INC.'lib/scripts/hotkeys.js',
        DOKU_INC . 'lib/scripts/behaviour.js',
        DOKU_INC . 'lib/scripts/page.js',
        tpl_incdir($tpl) . 'script.js',
    ];

    // add possible plugin scripts and userscript
    $files = array_merge($files, js_pluginscripts());
    if (is_array($config_cascade['userscript']['default'])) {
        foreach ($config_cascade['userscript']['default'] as $userscript) {
            $files[] = $userscript;
        }
    }

    // Let plugins decide to either put more scripts here or to remove some
    Event::createAndTrigger('JS_SCRIPT_LIST', $files);

    // The generated script depends on some dynamic options
    $cache = new Cache('scripts' . $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'] . md5(serialize($files)), '.js');
    $cache->setEvent('JS_CACHE_USE');

    $cache_files = array_merge($files, getConfigFiles('main'));
    $cache_files[] = __FILE__;

    // check cache age & handle conditional request
    // This may exit if a cache can be used
    $cache_ok = $cache->useCache(['files' => $cache_files]);
    http_cached($cache->cache, $cache_ok);

    // start output buffering and build the script
    ob_start();

    // add some global variables
    echo "var DOKU_BASE   = '" . DOKU_BASE . "';";
    echo "var DOKU_TPL    = '" . tpl_basedir($tpl) . "';";
    echo "var DOKU_COOKIE_PARAM = " . json_encode([
            'path' => empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'],
            'secure' => $conf['securecookie'] && is_ssl()
        ], JSON_THROW_ON_ERROR) . ";";
    // FIXME: Move those to JSINFO
    echo "Object.defineProperty(window, 'DOKU_UHN', { get: function() {" .
        "console.warn('Using DOKU_UHN is deprecated. Please use JSINFO.useHeadingNavigation instead');" .
        "return JSINFO.useHeadingNavigation; } });";
    echo "Object.defineProperty(window, 'DOKU_UHC', { get: function() {" .
        "console.warn('Using DOKU_UHC is deprecated. Please use JSINFO.useHeadingContent instead');" .
        "return JSINFO.useHeadingContent; } });";

    // load JS specific translations
    $lang['js']['plugins'] = js_pluginstrings();
    $templatestrings = js_templatestrings($tpl);
    if (!empty($templatestrings)) {
        $lang['js']['template'] = $templatestrings;
    }
    echo 'LANG = ' . json_encode($lang['js'], JSON_THROW_ON_ERROR) . ";\n";

    // load toolbar
    toolbar_JSdefines('toolbar');

    // load files
    foreach ($files as $file) {
        if (!file_exists($file)) continue;
        $ismin = str_ends_with($file, '.min.js');
        $debugjs = ($conf['allowdebug'] && strpos($file, DOKU_INC . 'lib/scripts/') !== 0);

        echo "\n\n/* XXXXXXXXXX begin of " . str_replace(DOKU_INC, '', $file) . " XXXXXXXXXX */\n\n";
        if ($ismin) echo "\n/* BEGIN NOCOMPRESS */\n";
        if ($debugjs) echo "\ntry {\n";
        js_load($file);
        if ($debugjs) echo "\n} catch (e) {\n   logError(e, '" . str_replace(DOKU_INC, '', $file) . "');\n}\n";
        if ($ismin) echo "\n/* END NOCOMPRESS */\n";
        echo "\n\n/* XXXXXXXXXX end of " . str_replace(DOKU_INC, '', $file) . " XXXXXXXXXX */\n\n";
    }

    // init stuff
    if ($conf['locktime'] != 0) {
        js_runonstart("dw_locktimer.init(" . ($conf['locktime'] - 60) . "," . $conf['usedraft'] . ")");
    }
    // init hotkeys - must have been done after init of toolbar
    # disabled for FS#1958    js_runonstart('initializeHotkeys()');

    // end output buffering and get contents
    $js = ob_get_contents();
    ob_end_clean();

    // strip any source maps
    stripsourcemaps($js);

    // compress whitespace and comments
    if ($conf['compress']) {
        try {
            $js = (new JSStrip())->compress($js);
        } catch (JSStripException $e) {
            $js .= "\nconsole.error(" . json_encode($e->getMessage(), JSON_THROW_ON_ERROR) . ");\n";
        }
    }

    $js .= "\n"; // https://bugzilla.mozilla.org/show_bug.cgi?id=316033

    http_cached_finish($cache->cache, $js);
}

/**
 * Load the given file, handle include calls and print it
 *
 * @param string $file filename path to file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_load($file)
{
    if (!file_exists($file)) return;
    static $loaded = [];

    $data = io_readFile($file);
    while (preg_match('#/\*\s*DOKUWIKI:include(_once)?\s+([\w\.\-_/]+)\s*\*/#', $data, $match)) {
        $ifile = $match[2];

        // is it a include_once?
        if ($match[1]) {
            $base = PhpString::basename($ifile);
            if (array_key_exists($base, $loaded) && $loaded[$base] === true) {
                $data = str_replace($match[0], '', $data);
                continue;
            }
            $loaded[$base] = true;
        }

        if ($ifile[0] != '/') $ifile = dirname($file) . '/' . $ifile;

        $idata = '';
        if (file_exists($ifile)) {
            $ismin = str_ends_with($ifile, '.min.js');
            if ($ismin) $idata .= "\n/* BEGIN NOCOMPRESS */\n";
            $idata .= io_readFile($ifile);
            if ($ismin) $idata .= "\n/* END NOCOMPRESS */\n";
        }
        $data = str_replace($match[0], $idata, $data);
    }
    echo "$data\n";
}

/**
 * Returns a list of possible Plugin Scripts (no existance check here)
 *
 * @return array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_pluginscripts()
{
    $list = [];
    $plugins = plugin_list();
    foreach ($plugins as $p) {
        $list[] = DOKU_PLUGIN . "$p/script.js";
    }
    return $list;
}

/**
 * Return an two-dimensional array with strings from the language file of each plugin.
 *
 * - $lang['js'] must be an array.
 * - Nothing is returned for plugins without an entry for $lang['js']
 *
 * @return array
 * @author Gabriel Birke <birke@d-scribe.de>
 *
 */
function js_pluginstrings()
{
    global $conf, $config_cascade;
    $pluginstrings = [];
    $plugins = plugin_list();
    foreach ($plugins as $p) {
        $path = DOKU_PLUGIN . $p . '/lang/';

        if (isset($lang)) unset($lang);
        if (file_exists($path . "en/lang.php")) {
            include $path . "en/lang.php";
        }
        foreach ($config_cascade['lang']['plugin'] as $config_file) {
            if (file_exists($config_file . $p . '/en/lang.php')) {
                include($config_file . $p . '/en/lang.php');
            }
        }
        if (isset($conf['lang']) && $conf['lang'] != 'en') {
            if (file_exists($path . $conf['lang'] . "/lang.php")) {
                include($path . $conf['lang'] . '/lang.php');
            }
            foreach ($config_cascade['lang']['plugin'] as $config_file) {
                if (file_exists($config_file . $p . '/' . $conf['lang'] . '/lang.php')) {
                    include($config_file . $p . '/' . $conf['lang'] . '/lang.php');
                }
            }
        }

        if (isset($lang['js'])) {
            $pluginstrings[$p] = $lang['js'];
        }
    }
    return $pluginstrings;
}

/**
 * Return an two-dimensional array with strings from the language file of current active template.
 *
 * - $lang['js'] must be an array.
 * - Nothing is returned for template without an entry for $lang['js']
 *
 * @param string $tpl
 * @return array
 */
function js_templatestrings($tpl)
{
    global $conf, $config_cascade;

    $path = tpl_incdir() . 'lang/';

    $templatestrings = [];
    if (file_exists($path . "en/lang.php")) {
        include $path . "en/lang.php";
    }
    foreach ($config_cascade['lang']['template'] as $config_file) {
        if (file_exists($config_file . $conf['template'] . '/en/lang.php')) {
            include($config_file . $conf['template'] . '/en/lang.php');
        }
    }
    if (isset($conf['lang']) && $conf['lang'] != 'en' && file_exists($path . $conf['lang'] . "/lang.php")) {
        include $path . $conf['lang'] . "/lang.php";
    }
    if (isset($conf['lang']) && $conf['lang'] != 'en') {
        if (file_exists($path . $conf['lang'] . "/lang.php")) {
            include $path . $conf['lang'] . "/lang.php";
        }
        foreach ($config_cascade['lang']['template'] as $config_file) {
            if (file_exists($config_file . $conf['template'] . '/' . $conf['lang'] . '/lang.php')) {
                include($config_file . $conf['template'] . '/' . $conf['lang'] . '/lang.php');
            }
        }
    }

    if (isset($lang['js'])) {
        $templatestrings[$tpl] = $lang['js'];
    }
    return $templatestrings;
}

/**
 * Escapes a String to be embedded in a JavaScript call, keeps \n
 * as newline
 *
 * @param string $string
 * @return string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_escape($string)
{
    return str_replace('\\\\n', '\\n', addslashes($string));
}

/**
 * Adds the given JavaScript code to the window.onload() event
 *
 * @param string $func
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function js_runonstart($func)
{
    echo "jQuery(function(){ $func; });" . NL;
}
