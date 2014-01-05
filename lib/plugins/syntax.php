<?php
/**
 * Syntax Plugin Prototype
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class DokuWiki_Syntax_Plugin extends Doku_Parser_Mode {

    var $allowedModesSetup = false;
    var $localised = false;         // set to true by setupLocale() after loading language dependent strings
    var $lang = array();            // array to hold language dependent strings, best accessed via ->getLang()
    var $configloaded = false;      // set to true by loadConfig() after loading plugin configuration variables
    var $conf = array();            // array to hold plugin settings, best accessed via ->getConf()

    /**
     * General Info
     *
     * Needs to return a associative array with the following values:
     *
     * author - Author of the plugin
     * email  - Email address to contact the author
     * date   - Last modified date of the plugin in YYYY-MM-DD format
     * name   - Name of the plugin
     * desc   - Short description of the plugin (Text only)
     * url    - Website with more information on the plugin (eg. syntax description)
     */
    function getInfo(){
        $parts = explode('_',get_class($this));
        $info  = DOKU_PLUGIN.'/'.$parts[2].'/plugin.info.txt';
        if(@file_exists($info)) return confToHash($info);
        trigger_error('getInfo() not implemented in '.get_class($this).' and '.$info.' not found', E_USER_WARNING);
        return array();
    }

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     */
    function getType(){
        trigger_error('getType() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Allowed Mode Types
     *
     * Defines the mode types for other dokuwiki markup that maybe nested within the
     * plugin's own markup. Needs to return an array of one or more of the mode types
     * defined in $PARSER_MODES in parser.php
     */
    function getAllowedTypes() {
        return array();
    }

    /**
     * Paragraph Type
     *
     * Defines how this syntax is handled regarding paragraphs. This is important
     * for correct XHTML nesting. Should return one of the following:
     *
     * 'normal' - The plugin can be used inside paragraphs
     * 'block'  - Open paragraphs need to be closed before plugin output
     * 'stack'  - Special case. Plugin wraps other paragraphs.
     *
     * @see Doku_Handler_Block
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * This function can only pass data to render() via its return value - render()
     * may be not be run during the object's current life.
     *
     * Usually you should only need the $match param.
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler Reference to the Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    function handle($match, $state, $pos, Doku_Handler &$handler){
        trigger_error('handle() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Handles the actual output creation.
     *
     * The function must not assume any other of the classes methods have been run
     * during the object's current life. The only reliable data it receives are its
     * parameters.
     *
     * The function should always check for the given output format and return false
     * when a format isn't supported.
     *
     * $renderer contains a reference to the renderer object which is
     * currently handling the rendering. You need to use it for writing
     * the output. How this is done depends on the renderer used (specified
     * by $format
     *
     * The contents of the $data array depends on what the handler() function above
     * created
     *
     * @param   $format   string        output format being rendered
     * @param   $renderer Doku_Renderer reference to the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    function render($format, Doku_Renderer &$renderer, $data) {
        trigger_error('render() not implemented in '.get_class($this), E_USER_WARNING);

    }

    /**
     *  There should be no need to override these functions
     */
    function accepts($mode) {

        if (!$this->allowedModesSetup) {
            global $PARSER_MODES;

            $allowedModeTypes = $this->getAllowedTypes();
            foreach($allowedModeTypes as $mt) {
                $this->allowedModes = array_merge($this->allowedModes, $PARSER_MODES[$mt]);
            }

            $idx = array_search(substr(get_class($this), 7), (array) $this->allowedModes);
            if ($idx !== false) {
                unset($this->allowedModes[$idx]);
            }
            $this->allowedModesSetup = true;
        }

        return parent::accepts($mode);
    }

    // plugin introspection methods
    // extract from class name, format = <plugin type>_plugin_<name>[_<component name>]
    function getPluginType() { list($t) = explode('_', get_class($this), 2); return $t;  }
    function getPluginName() { list($t, $p, $n) = explode('_', get_class($this), 4); return $n; }

    /**
     * Get the name of the component of the current class
     *
     * @return string component name
     */
    function getPluginComponent() { list($t, $p, $n, $c) = explode('_', get_class($this), 4); return (isset($c)?$c:''); }

    // localisation methods
    /**
     * getLang($id)
     *
     * use this function to access plugin language strings
     * to try to minimise unnecessary loading of the strings when the plugin doesn't require them
     * e.g. when info plugin is querying plugins for information about themselves.
     *
     * @param   string $id     id of the string to be retrieved
     * @return  string  string in appropriate language or english if not available
     */
    function getLang($id) {
        if (!$this->localised) $this->setupLocale();

        return (isset($this->lang[$id]) ? $this->lang[$id] : '');
    }

    /**
     * locale_xhtml($id)
     *
     * retrieve a language dependent wiki page and pass to xhtml renderer for display
     * plugin equivalent of p_locale_xhtml()
     *
     * @param   string  $id     id of language dependent wiki page
     * @return  string  parsed contents of the wiki page in xhtml format
     */
    function locale_xhtml($id) {
        return p_cached_output($this->localFN($id));
    }

    /**
     * localFN($id)
     * prepends appropriate path for a language dependent filename
     * plugin equivalent of localFN()
     */
    function localFN($id) {
        global $conf;
        $plugin = $this->getPluginName();
        $file = DOKU_CONF.'/plugin_lang/'.$plugin.'/'.$conf['lang'].'/'.$id.'.txt';
        if (!@file_exists($file)){
            $file = DOKU_PLUGIN.$plugin.'/lang/'.$conf['lang'].'/'.$id.'.txt';
            if(!@file_exists($file)){
                //fall back to english
                $file = DOKU_PLUGIN.$plugin.'/lang/en/'.$id.'.txt';
            }
        }
        return $file;
    }

    /**
     *  setupLocale()
     *  reads all the plugins language dependent strings into $this->lang
     *  this function is automatically called by getLang()
     */
    function setupLocale() {
        if ($this->localised) return;

        global $conf;            // definitely don't invoke "global $lang"
        $path = DOKU_PLUGIN.$this->getPluginName().'/lang/';

        $lang = array();
        // don't include once, in case several plugin components require the same language file
        @include($path.'en/lang.php');
        if ($conf['lang'] != 'en') @include($path.$conf['lang'].'/lang.php');

        $this->lang = $lang;
        $this->localised = true;
    }

    // configuration methods
    /**
     * getConf($setting)
     *
     * use this function to access plugin configuration variables
     */
    function getConf($setting) {

        if(!$this->configloaded) { $this->loadConfig(); }

        return $this->conf[$setting];
    }

    /**
     * loadConfig()
     * merges the plugin's default settings with any local settings
     * this function is automatically called through getConf()
     */
    function loadConfig() {
        global $conf;

        $defaults = $this->readDefaultSettings();
        $plugin   = $this->getPluginName();

        foreach($defaults as $key => $value) {
            if(isset($conf['plugin'][$plugin][$key])) continue;
            $conf['plugin'][$plugin][$key] = $value;
        }

        $this->configloaded = true;
        $this->conf         =& $conf['plugin'][$plugin];
    }

    /**
     * read the plugin's default configuration settings from conf/default.php
     * this function is automatically called through getConf()
     *
     * @return    array    setting => value
     */
    function readDefaultSettings() {

        $path = DOKU_PLUGIN.$this->getPluginName().'/conf/';
        $conf = array();

        if(@file_exists($path.'default.php')) {
            include($path.'default.php');
        }

        return $conf;
    }

    /**
     * Loads a given helper plugin (if enabled)
     *
     * @author  Esther Brunner <wikidesign@gmail.com>
     *
     * @param   string $name   name of plugin to load
     * @param   bool   $msg    if a message should be displayed in case the plugin is not available
     *
     * @return  object  helper plugin object
     */
    function loadHelper($name, $msg = true) {
        if(!plugin_isdisabled($name)) {
            $obj = plugin_load('helper', $name);
        } else {
            $obj = null;
        }
        if(is_null($obj) && $msg) msg("Helper plugin $name is not available or invalid.", -1);
        return $obj;
    }

    /**
     * Allow the plugin to prevent DokuWiki from reusing an instance
     *
     * @return bool   false if the plugin has to be instantiated
     */
    function isSingleton() {
        return true;
    }

}
//Setup VIM: ex: et ts=4 :
