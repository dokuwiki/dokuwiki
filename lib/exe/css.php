<?php

/**
 * DokuWiki StyleSheet creator
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use LesserPHP\Lessc;
use dokuwiki\StyleUtils;
use dokuwiki\Cache\Cache;
use dokuwiki\Extension\Event;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('NOSESSION')) define('NOSESSION', true); // we do not use a session or authentication here (better caching)
if (!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT', 1); // we gzip ourself here
if (!defined('NL')) define('NL', "\n");
require_once(DOKU_INC . 'inc/init.php');

// Main (don't run when UNIT test)
if (!defined('SIMPLE_TEST')) {
    header('Content-Type: text/css; charset=utf-8');
    css_out();
}


// ---------------------- functions ------------------------------

/**
 * Output all needed Styles
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_out()
{
    global $conf;
    global $lang;
    global $config_cascade;
    global $INPUT;

    if ($INPUT->str('s') == 'feed') {
        $mediatypes = ['feed'];
        $type = 'feed';
    } else {
        $mediatypes = ['screen', 'all', 'print', 'speech'];
        $type = '';
    }

    // decide from where to get the template
    $tpl = trim(preg_replace('/[^\w-]+/', '', $INPUT->str('t')));
    if (!$tpl) {
        $tpl = $conf['template'];
    }

    // load style.ini
    $styleUtil = new StyleUtils($tpl, $INPUT->bool('preview'));
    $styleini = $styleUtil->cssStyleini();

    // cache influencers
    $tplinc = tpl_incdir($tpl);
    $cache_files = getConfigFiles('main');
    $cache_files[] = $tplinc . 'style.ini';
    $cache_files[] = DOKU_CONF . "tpl/$tpl/style.ini";
    $cache_files[] = __FILE__;
    if ($INPUT->bool('preview')) {
        $cache_files[] = $conf['cachedir'] . '/preview.ini';
    }

    // Array of needed files and their web locations, the latter ones
    // are needed to fix relative paths in the stylesheets
    $media_files = [];
    foreach ($mediatypes as $mediatype) {
        $files = [];

        // load core styles
        $files[DOKU_INC . 'lib/styles/' . $mediatype . '.css'] = DOKU_BASE . 'lib/styles/';

        // load jQuery-UI theme
        if ($mediatype == 'screen') {
            $files[DOKU_INC . 'lib/scripts/jquery/jquery-ui-theme/smoothness.css'] =
                DOKU_BASE . 'lib/scripts/jquery/jquery-ui-theme/';
        }
        // load plugin styles
        $files = array_merge($files, css_pluginstyles($mediatype));
        // load template styles
        if (isset($styleini['stylesheets'][$mediatype])) {
            $files = array_merge($files, $styleini['stylesheets'][$mediatype]);
        }
        // load user styles
        if (isset($config_cascade['userstyle'][$mediatype]) && is_array($config_cascade['userstyle'][$mediatype])) {
            foreach ($config_cascade['userstyle'][$mediatype] as $userstyle) {
                $files[$userstyle] = DOKU_BASE;
            }
        }

        // Let plugins decide to either put more styles here or to remove some
        $media_files[$mediatype] = css_filewrapper($mediatype, $files);
        $CSSEvt = new Event('CSS_STYLES_INCLUDED', $media_files[$mediatype]);

        // Make it preventable.
        if ($CSSEvt->advise_before()) {
            $cache_files = array_merge($cache_files, array_keys($media_files[$mediatype]['files']));
        } else {
            // unset if prevented. Nothing will be printed for this mediatype.
            unset($media_files[$mediatype]);
        }

        // finish event.
        $CSSEvt->advise_after();
    }

    // The generated script depends on some dynamic options
    $cache = new Cache(
        'styles' .
        $_SERVER['HTTP_HOST'] .
        $_SERVER['SERVER_PORT'] .
        $INPUT->bool('preview') .
        DOKU_BASE .
        $tpl .
        $type,
        '.css'
    );
    $cache->setEvent('CSS_CACHE_USE');

    // check cache age & handle conditional request
    // This may exit if a cache can be used
    $cache_ok = $cache->useCache(['files' => $cache_files]);
    http_cached($cache->cache, $cache_ok);

    // start output buffering
    ob_start();

    // Fire CSS_STYLES_INCLUDED for one last time to let the
    // plugins decide whether to include the DW default styles.
    // This can be done by preventing the Default.
    $media_files['DW_DEFAULT'] = css_filewrapper('DW_DEFAULT');
    Event::createAndTrigger('CSS_STYLES_INCLUDED', $media_files['DW_DEFAULT'], 'css_defaultstyles');

    // build the stylesheet
    foreach ($mediatypes as $mediatype) {
        // Check if there is a wrapper set for this type.
        if (!isset($media_files[$mediatype])) {
            continue;
        }

        $cssData = $media_files[$mediatype];

        // Print the styles.
        echo NL;
        if ($cssData['encapsulate'] === true) {
            echo $cssData['encapsulationPrefix'] . ' {';
        }
        echo '/* START ' . $cssData['mediatype'] . ' styles */' . NL;

        // load files
        foreach ($cssData['files'] as $file => $location) {
            $display = str_replace(fullpath(DOKU_INC), '', fullpath($file));
            echo "\n/* XXXXXXXXX $display XXXXXXXXX */\n";
            echo css_loadfile($file, $location);
        }

        echo NL;
        if ($cssData['encapsulate'] === true) {
            echo '} /* /@media ';
        } else {
            echo '/*';
        }
        echo ' END ' . $cssData['mediatype'] . ' styles */' . NL;
    }

    // end output buffering and get contents
    $css = ob_get_contents();
    ob_end_clean();

    // strip any source maps
    stripsourcemaps($css);

    // apply style replacements
    $css = css_applystyle($css, $styleini['replacements']);

    // parse less
    $css = css_parseless($css);

    // compress whitespace and comments
    if ($conf['compress']) {
        $css = css_compress($css);
    }

    // embed small images right into the stylesheet
    if ($conf['cssdatauri']) {
        $base = preg_quote(DOKU_BASE, '#');
        $css = preg_replace_callback('#(url\([ \'"]*)(' . $base . ')(.*?(?:\.(png|gif)))#i', 'css_datauri', $css);
    }

    http_cached_finish($cache->cache, $css);
}

/**
 * Uses phpless to parse LESS in our CSS
 *
 * most of this function is error handling to show a nice useful error when
 * LESS compilation fails
 *
 * @param string $css
 * @return string
 */
function css_parseless($css)
{
    global $conf;

    $less = new Lessc();
    $less->setImportDir([DOKU_INC]);
    $less->setPreserveComments(!$conf['compress']);

    if (defined('DOKU_UNITTEST')) {
        $less->addImportDir(TMP_DIR);
    }

    try {
        return $less->compile($css);
    } catch (Exception $e) {
        // get exception message
        $msg = str_replace(["\n", "\r", "'"], [], $e->getMessage());

        // try to use line number to find affected file
        if (preg_match('/line: (\d+)$/', $msg, $m)) {
            $msg = substr($msg, 0, -1 * strlen($m[0])); //remove useless linenumber
            $lno = $m[1];

            // walk upwards to last include
            $lines = explode("\n", $css);
            for ($i = $lno - 1; $i >= 0; $i--) {
                if (preg_match('/\/(\* XXXXXXXXX )(.*?)( XXXXXXXXX \*)\//', $lines[$i], $m)) {
                    // we found it, add info to message
                    $msg .= ' in ' . $m[2] . ' at line ' . ($lno - $i);
                    break;
                }
            }
        }

        // something went wrong
        $error = 'A fatal error occured during compilation of the CSS files. ' .
            'If you recently installed a new plugin or template it ' .
            'might be broken and you should try disabling it again. [' . $msg . ']';

        echo ".dokuwiki:before {
            content: '$error';
            background-color: red;
            display: block;
            background-color: #fcc;
            border-color: #ebb;
            color: #000;
            padding: 0.5em;
        }";

        exit;
    }
}

/**
 * Does placeholder replacements in the style according to
 * the ones defined in a templates style.ini file
 *
 * This also adds the ini defined placeholders as less variables
 * (sans the surrounding __ and with a ini_ prefix)
 *
 * @param string $css
 * @param array $replacements array(placeholder => value)
 * @return string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_applystyle($css, $replacements)
{
    // we convert ini replacements to LESS variable names
    // and build a list of variable: value; pairs
    $less = '';
    foreach ((array)$replacements as $key => $value) {
        $lkey = trim($key, '_');
        $lkey = '@ini_' . $lkey;
        $less .= "$lkey: $value;\n";

        $replacements[$key] = $lkey;
    }

    // we now replace all old ini replacements with LESS variables
    $css = strtr($css, $replacements);

    // now prepend the list of LESS variables as the very first thing
    $css = $less . $css;
    return $css;
}

/**
 * Wrapper for the files, content and mediatype for the event CSS_STYLES_INCLUDED
 *
 * @param string $mediatype type ofthe current media files/content set
 * @param array $files set of files that define the current mediatype
 * @return array
 *
 * @author Gerry Weißbach <gerry.w@gammaproduction.de>
 */
function css_filewrapper($mediatype, $files = [])
{
    return [
        'files' => $files,
        'mediatype' => $mediatype,
        'encapsulate' => $mediatype != 'all',
        'encapsulationPrefix' => '@media ' . $mediatype
    ];
}

/**
 * Prints the @media encapsulated default styles of DokuWiki
 *
 * This function is being called by a CSS_STYLES_INCLUDED event
 * The event can be distinguished by the mediatype which is:
 *   DW_DEFAULT
 *
 * @author Gerry Weißbach <gerry.w@gammaproduction.de>
 */
function css_defaultstyles()
{
    // print the default classes for interwiki links and file downloads
    echo '@media screen {';
    css_interwiki();
    css_filetypes();
    echo '}';
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
function css_interwiki()
{

    // default style
    echo 'a.interwiki {';
    echo ' background: transparent url(' . DOKU_BASE . 'lib/images/interwiki.svg) 0 0 no-repeat;';
    echo ' background-size: 1.2em;';
    echo ' padding: 0 0 0 1.4em;';
    echo '}';

    // additional styles when icon available
    $iwlinks = getInterwiki();
    foreach (array_keys($iwlinks) as $iw) {
        $class = preg_replace('/[^_\-a-z0-9]+/i', '_', $iw);
        foreach (['svg', 'png', 'gif'] as $ext) {
            $file = 'lib/images/interwiki/' . $iw . '.' . $ext;

            if (file_exists(DOKU_INC . $file)) {
                echo "a.iw_$class {";
                echo '  background-image: url(' . DOKU_BASE . $file . ')';
                echo '}';
                break;
            }
        }
    }
}

/**
 * Prints classes for file download links
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function css_filetypes()
{

    // default style
    echo '.mediafile {';
    echo ' background: transparent url(' . DOKU_BASE . 'lib/images/fileicons/svg/file.svg) 0px 1px no-repeat;';
    echo ' background-size: 1.2em;';
    echo ' padding-left: 1.5em;';
    echo '}';

    // additional styles when icon available
    // scan directory for all icons
    $exts = [];
    if ($dh = opendir(DOKU_INC . 'lib/images/fileicons/svg')) {
        while (false !== ($file = readdir($dh))) {
            if (preg_match('/(.*?)\.svg$/i', $file, $match)) {
                $exts[] = strtolower($match[1]);
            }
        }
        closedir($dh);
    }
    foreach ($exts as $ext) {
        $class = preg_replace('/[^_\-a-z0-9]+/', '_', $ext);
        echo ".mf_$class {";
        echo '  background-image: url(' . DOKU_BASE . 'lib/images/fileicons/svg/' . $ext . '.svg)';
        echo '}';
    }
}

/**
 * Loads a given file and fixes relative URLs with the
 * given location prefix
 *
 * @param string $file file system path
 * @param string $location
 * @return string
 */
function css_loadfile($file, $location = '')
{
    $css_file = new DokuCssFile($file);
    return $css_file->load($location);
}

/**
 * Helper class to abstract loading of css/less files
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 */
class DokuCssFile
{
    protected $filepath;             // file system path to the CSS/Less file
    protected $location;             // base url location of the CSS/Less file
    protected $relative_path;

    public function __construct($file)
    {
        $this->filepath = $file;
    }

    /**
     * Load the contents of the css/less file and adjust any relative paths/urls (relative to this file) to be
     * relative to the dokuwiki root: the web root (DOKU_BASE) for most files; the file system root (DOKU_INC)
     * for less files.
     *
     * @param string $location base url for this file
     * @return string the CSS/Less contents of the file
     */
    public function load($location = '')
    {
        if (!file_exists($this->filepath)) return '';

        $css = io_readFile($this->filepath);
        if (!$location) return $css;

        $this->location = $location;

        $css = preg_replace_callback('#(url\( *)([\'"]?)(.*?)(\2)( *\))#', [$this, 'replacements'], $css);
        $css = preg_replace_callback('#(@import\s+)([\'"])(.*?)(\2)#', [$this, 'replacements'], $css);

        return $css;
    }

    /**
     * Get the relative file system path of this file, relative to dokuwiki's root folder, DOKU_INC
     *
     * @return string   relative file system path
     */
    protected function getRelativePath()
    {

        if (is_null($this->relative_path)) {
            $basedir = [DOKU_INC];

            // during testing, files may be found relative to a second base dir, TMP_DIR
            if (defined('DOKU_UNITTEST')) {
                $basedir[] = realpath(TMP_DIR);
            }

            $basedir = array_map('preg_quote_cb', $basedir);
            $regex = '/^(' . implode('|', $basedir) . ')/';
            $this->relative_path = preg_replace($regex, '', dirname($this->filepath));
        }

        return $this->relative_path;
    }

    /**
     * preg_replace callback to adjust relative urls from relative to this file to relative
     * to the appropriate dokuwiki root location as described in the code
     *
     * @param array $match see http://php.net/preg_replace_callback
     * @return string   see http://php.net/preg_replace_callback
     */
    public function replacements($match)
    {

        if (preg_match('#^(/|data:|https?://)#', $match[3])) { // not a relative url? - no adjustment required
            return $match[0];
        } elseif (str_ends_with($match[3], '.less')) { // a less file import? - requires a file system location
            if ($match[3][0] != '/') {
                $match[3] = $this->getRelativePath() . '/' . $match[3];
            }
        } else { // everything else requires a url adjustment
            $match[3] = $this->location . $match[3];
        }

        return implode('', array_slice($match, 1));
    }
}

/**
 * Convert local image URLs to data URLs if the filesize is small
 *
 * Callback for preg_replace_callback
 *
 * @param array $match
 * @return string
 */
function css_datauri($match)
{
    global $conf;

    $pre = unslash($match[1]);
    $base = unslash($match[2]);
    $url = unslash($match[3]);
    $ext = unslash($match[4]);

    $local = DOKU_INC . $url;
    $size = @filesize($local);
    if ($size && $size < $conf['cssdatauri']) {
        $data = base64_encode(file_get_contents($local));
    }
    if (!empty($data)) {
        $url = 'data:image/' . $ext . ';base64,' . $data;
    } else {
        $url = $base . $url;
    }
    return $pre . $url;
}


/**
 * Returns a list of possible Plugin Styles (no existance check here)
 *
 * @param string $mediatype
 * @return array
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 */
function css_pluginstyles($mediatype = 'screen')
{
    $list = [];
    $plugins = plugin_list();
    foreach ($plugins as $p) {
        $list[DOKU_PLUGIN . "$p/$mediatype.css"] = DOKU_BASE . "lib/plugins/$p/";
        $list[DOKU_PLUGIN . "$p/$mediatype.less"] = DOKU_BASE . "lib/plugins/$p/";
        // alternative for screen.css
        if ($mediatype == 'screen') {
            $list[DOKU_PLUGIN . "$p/style.css"] = DOKU_BASE . "lib/plugins/$p/";
            $list[DOKU_PLUGIN . "$p/style.less"] = DOKU_BASE . "lib/plugins/$p/";
        }
    }
    return $list;
}

/**
 * Very simple CSS optimizer
 *
 * @param string $css
 * @return string
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 */
function css_compress($css)
{
    // replace quoted strings with placeholder
    $quote_storage = [];

    $quote_cb = function ($match) use (&$quote_storage) {
        $quote_storage[] = $match[0];
        return '"STR' . (count($quote_storage) - 1) . '"';
    };

    $css = preg_replace_callback('/(([\'"]).*?(?<!\\\\)\2)/', $quote_cb, $css);

    // strip comments through a callback
    $css = preg_replace_callback('#(/\*)(.*?)(\*/)#s', 'css_comment_cb', $css);

    // strip (incorrect but common) one line comments
    $css = preg_replace_callback('/^.*\/\/.*$/m', 'css_onelinecomment_cb', $css);

    // strip whitespaces
    $css = preg_replace('![\r\n\t ]+!', ' ', $css);
    $css = preg_replace('/ ?([;,{}\/]) ?/', '\\1', $css);
    $css = preg_replace('/ ?: /', ':', $css);

    // number compression
    $css = preg_replace(
        '/([: ])0+(\.\d+?)0*((?:pt|pc|in|mm|cm|em|ex|px)\b|%)(?=[^\{]*[;\}])/',
        '$1$2$3',
        $css
    ); // "0.1em" to ".1em", "1.10em" to "1.1em"
    $css = preg_replace(
        '/([: ])\.(0)+((?:pt|pc|in|mm|cm|em|ex|px)\b|%)(?=[^\{]*[;\}])/',
        '$1$2',
        $css
    ); // ".0em" to "0"
    $css = preg_replace(
        '/([: ]0)0*(\.0*)?((?:pt|pc|in|mm|cm|em|ex|px)(?=[^\{]*[;\}])\b|%)/',
        '$1',
        $css
    ); // "0.0em" to "0"
    $css = preg_replace(
        '/([: ]\d+)(\.0*)((?:pt|pc|in|mm|cm|em|ex|px)(?=[^\{]*[;\}])\b|%)/',
        '$1$3',
        $css
    ); // "1.0em" to "1em"
    $css = preg_replace(
        '/([: ])0+(\d+|\d*\.\d+)((?:pt|pc|in|mm|cm|em|ex|px)(?=[^\{]*[;\}])\b|%)/',
        '$1$2$3',
        $css
    ); // "001em" to "1em"

    // shorten attributes (1em 1em 1em 1em -> 1em)
    $css = preg_replace(
        '/(?<![\w\-])((?:margin|padding|border|border-(?:width|radius)):)([\w\.]+)( \2)+(?=[;\}]| !)/',
        '$1$2',
        $css
    ); // "1em 1em 1em 1em" to "1em"
    $css = preg_replace(
        '/(?<![\w\-])((?:margin|padding|border|border-(?:width)):)([\w\.]+) ([\w\.]+) \2 \3(?=[;\}]| !)/',
        '$1$2 $3',
        $css
    ); // "1em 2em 1em 2em" to "1em 2em"

    // shorten colors
    $css = preg_replace(
        "/#([0-9a-fA-F]{1})\\1([0-9a-fA-F]{1})\\2([0-9a-fA-F]{1})\\3(?=[^\{]*[;\}])/",
        "#\\1\\2\\3",
        $css
    );

    // replace back protected strings
    $quote_back_cb = function ($match) use (&$quote_storage) {
        return $quote_storage[$match[1]];
    };

    $css = preg_replace_callback('/"STR(\d+)"/', $quote_back_cb, $css);
    $css = trim($css);

    return $css;
}

/**
 * Callback for css_compress()
 *
 * Keeps short comments (< 5 chars) to maintain typical browser hacks
 *
 * @param array $matches
 * @return string
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 */
function css_comment_cb($matches)
{
    if (strlen($matches[2]) > 4) return '';
    return $matches[0];
}

/**
 * Callback for css_compress()
 *
 * Strips one line comments but makes sure it will not destroy url() constructs with slashes
 *
 * @param array $matches
 * @return string
 */
function css_onelinecomment_cb($matches)
{
    $line = $matches[0];

    $i = 0;
    $len = strlen($line);

    while ($i < $len) {
        $nextcom = strpos($line, '//', $i);
        $nexturl = stripos($line, 'url(', $i);

        if ($nextcom === false) {
            // no more comments, we're done
            $i = $len;
            break;
        }

        if ($nexturl === false || $nextcom < $nexturl) {
            // no url anymore, strip comment and be done
            $i = $nextcom;
            break;
        }

        // we have an upcoming url
        $i = strpos($line, ')', $nexturl);
    }

    return substr($line, 0, $i);
}

//Setup VIM: ex: et ts=4 :
