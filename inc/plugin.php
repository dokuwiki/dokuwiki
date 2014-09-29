<?php
/**
 * DokuWiki Plugin base class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

/**
 * Do not inherit directly from this class, instead inherit from the specialized
 * ones in lib/plugin
 */
class DokuWiki_Plugin {

    var $localised = false;        // set to true by setupLocale() after loading language dependent strings
    var $lang = array();           // array to hold language dependent strings, best accessed via ->getLang()
    var $configloaded = false;     // set to true by loadConfig() after loading plugin configuration variables
    var $conf = array();           // array to hold plugin settings, best accessed via ->getConf()

    /**
     * General Info
     *
     * Needs to return a associative array with the following values:
     *
     * base   - the plugin's base name (eg. the directory it needs to be installed in)
     * author - Author of the plugin
     * email  - Email address to contact the author
     * date   - Last modified date of the plugin in YYYY-MM-DD format
     * name   - Name of the plugin
     * desc   - Short description of the plugin (Text only)
     * url    - Website with more information on the plugin (eg. syntax description)
     */
    public function getInfo(){
        $parts = explode('_', get_class($this));
        $info = DOKU_PLUGIN . '/' . $parts[2] . '/plugin.info.txt';
        if(@file_exists($info)) return confToHash($info);

        msg(
            'getInfo() not implemented in ' . get_class($this) . ' and ' . $info . ' not found.<br />' .
            'Verify you\'re running the latest version of the plugin. If the problem persists, send a ' .
            'bug report to the author of the ' . $parts[2] . ' plugin.', -1
        );
        return array(
            'date' => '0000-00-00',
            'name' => $parts[2] . ' plugin',
        );
    }

    // plugin introspection methods
    // extract from class name, format = <plugin type>_plugin_<name>[_<component name>]
    public function getPluginType() {
        list($t) = explode('_', get_class($this), 2);
        return $t;
    }
    public function getPluginName() {
        list(/* $t */, /* $p */, $n) = explode('_', get_class($this), 4);
        return $n;
    }
    public function getPluginComponent() {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', get_class($this), 4);
        return (isset($c)?$c:'');
    }

    // localisation methods
    /**
     * getLang($id)
     * use this function to access plugin language strings
     * to try to minimise unnecessary loading of the strings when the plugin doesn't require them
     * e.g. when info plugin is querying plugins for information about themselves.
     *
     * @param   string  $id     id of the string to be retrieved
     * @return  string  string in appropriate language or english if not available
     */
    public function getLang($id) {
        if (!$this->localised) $this->setupLocale();

        return (isset($this->lang[$id]) ? $this->lang[$id] : '');
    }

    /**
     * locale_xhtml($id)
     *
     * retrieve a language dependent file and pass to xhtml renderer for display
     * plugin equivalent of p_locale_xhtml()
     *
     * @param   string $id id of language dependent wiki page
     * @return  string     parsed contents of the wiki page in xhtml format
     */
    public function locale_xhtml($id) {
        return p_cached_output($this->localFN($id));
    }

    /**
     * localFN($id)
     * prepends appropriate path for a language dependent filename
     * plugin equivalent of localFN()
     */
    public function localFN($id) {
        global $conf;
        $plugin = $this->getPluginName();
        $file = DOKU_CONF.'plugin_lang/'.$plugin.'/'.$conf['lang'].'/'.$id.'.txt';
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
        if($this->localised) return;

        global $conf, $config_cascade; // definitely don't invoke "global $lang"
        $path = DOKU_PLUGIN . $this->getPluginName() . '/lang/';

        $lang = array();

        // don't include once, in case several plugin components require the same language file
        @include($path . 'en/lang.php');
        foreach($config_cascade['lang']['plugin'] as $config_file) {
            if(@file_exists($config_file . $this->getPluginName() . '/en/lang.php')) {
                include($config_file . $this->getPluginName() . '/en/lang.php');
            }
        }

        if($conf['lang'] != 'en') {
            @include($path . $conf['lang'] . '/lang.php');
            foreach($config_cascade['lang']['plugin'] as $config_file) {
                if(@file_exists($config_file . $this->getPluginName() . '/' . $conf['lang'] . '/lang.php')) {
                    include($config_file . $this->getPluginName() . '/' . $conf['lang'] . '/lang.php');
                }
            }
        }

        $this->lang = $lang;
        $this->localised = true;
    }

    // configuration methods
    /**
     * getConf($setting)
     *
     * use this function to access plugin configuration variables
     *
     * @param string $setting the setting to access
     * @param mixed  $notset  what to return if the setting is not available
     * @return mixed
     */
    public function getConf($setting, $notset=false){

        if (!$this->configloaded){ $this->loadConfig(); }

        if(isset($this->conf[$setting])){
            return $this->conf[$setting];
        }else{
            return $notset;
        }
    }

    /**
     * loadConfig()
     * merges the plugin's default settings with any local settings
     * this function is automatically called through getConf()
     */
    function loadConfig(){
        global $conf;

        $defaults = $this->readDefaultSettings();
        $plugin = $this->getPluginName();

        foreach ($defaults as $key => $value) {
            if (isset($conf['plugin'][$plugin][$key])) continue;
            $conf['plugin'][$plugin][$key] = $value;
        }

        $this->configloaded = true;
        $this->conf =& $conf['plugin'][$plugin];
    }

    /**
     * read the plugin's default configuration settings from conf/default.php
     * this function is automatically called through getConf()
     *
     * @return    array    setting => value
     */
    protected function readDefaultSettings() {

        $path = DOKU_PLUGIN.$this->getPluginName().'/conf/';
        $conf = array();

        if (@file_exists($path.'default.php')) {
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
    public function loadHelper($name, $msg = true){
        $obj = plugin_load('helper',$name);
        if (is_null($obj) && $msg) msg("Helper plugin $name is not available or invalid.",-1);
        return $obj;
    }

    // standard functions for outputing email addresses and links
    // use these to avoid having to duplicate code to produce links in line with the installation configuration

    /**
     * email
     * standardised function to generate an email link according to obfuscation settings
     */
    public function email($email, $name='', $class='', $more='') {
        if (!$email) return $name;
        $email = obfuscate($email);
        if (!$name) $name = $email;
        $class = "class='".($class ? $class : 'mail')."'";
        return "<a href='mailto:$email' $class title='$email' $more>$name</a>";
    }

    /**
     * external_link
     * standardised function to generate an external link according to conf settings
     */
    public function external_link($link, $title='', $class='', $target='', $more='') {
        global $conf;

        $link = htmlentities($link);
        if (!$title) $title = $link;
        if (!$target) $target = $conf['target']['extern'];
        if ($conf['relnofollow']) $more .= ' rel="nofollow"';

        if ($class) $class = " class='$class'";
        if ($target) $target = " target='$target'";
        if ($more) $more = " ".trim($more);

        return "<a href='$link'$class$target$more>$title</a>";
    }

    /**
     * A fallback to provide access to the old render() method
     *
     * Since syntax plugins provide their own render method with a different signature and they now
     * inherit from Doku_Plugin we can no longer have a render() method here (Strict Standards Violation).
     * Instead use render_text()
     *
     * @deprecated 2014-01-22
     * @param $name
     * @param $arguments
     * @return null|string
     */
    public function __call($name, $arguments) {
        if($name == 'render'){
            dbg_deprecated('render_text()');
            if(!isset($arguments[1])) $arguments[1] = 'xhtml';
            return $this->render_text($arguments[0], $arguments[1]);
        }
        trigger_error("no such method $name", E_USER_ERROR);
        return null;
    }

    /**
     * output text string through the parser, allows dokuwiki markup to be used
     * very ineffecient for small pieces of data - try not to use
     *
     * @param string $text wiki markup to parse
     * @param string $format output format
     * @return null|string
     */
    public function render_text($text, $format='xhtml') {
        return p_render($format, p_get_instructions($text),$info);
    }

    /**
     * Allow the plugin to prevent DokuWiki from reusing an instance
     *
     * @return bool   false if the plugin has to be instantiated
     */
    public function isSingleton() {
        return true;
    }
}
