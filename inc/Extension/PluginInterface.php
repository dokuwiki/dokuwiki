<?php

namespace dokuwiki\Extension;

/**
 * DokuWiki Plugin Interface
 *
 * Defines the public contract all DokuWiki plugins will adhere to. The actual code
 * to do so is defined in dokuwiki\Extension\PluginTrait
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
interface PluginInterface
{
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
    public function getInfo();

    /**
     * The type of the plugin inferred from the class name
     *
     * @return string  plugin type
     */
    public function getPluginType();

    /**
     * The name of the plugin inferred from the class name
     *
     * @return string  plugin name
     */
    public function getPluginName();

    /**
     * The component part of the plugin inferred from the class name
     *
     * @return string component name
     */
    public function getPluginComponent();

    /**
     * Access plugin language strings
     *
     * to try to minimise unnecessary loading of the strings when the plugin doesn't require them
     * e.g. when info plugin is querying plugins for information about themselves.
     *
     * @param   string $id id of the string to be retrieved
     * @return  string in appropriate language or english if not available
     */
    public function getLang($id);

    /**
     * retrieve a language dependent file and pass to xhtml renderer for display
     * plugin equivalent of p_locale_xhtml()
     *
     * @param   string $id id of language dependent wiki page
     * @return  string parsed contents of the wiki page in xhtml format
     */
    public function locale_xhtml($id);

    /**
     * Prepends appropriate path for a language dependent filename
     * plugin equivalent of localFN()
     *
     * @param string $id id of localization file
     * @param  string $ext The file extension (usually txt)
     * @return string wiki text
     */
    public function localFN($id, $ext = 'txt');

    /**
     * Reads all the plugins language dependent strings into $this->lang
     * this function is automatically called by getLang()
     *
     * @todo this could be made protected and be moved to the trait only
     */
    public function setupLocale();

    /**
     * use this function to access plugin configuration variables
     *
     * @param string $setting the setting to access
     * @param mixed $notset what to return if the setting is not available
     * @return mixed
     */
    public function getConf($setting, $notset = false);

    /**
     * merges the plugin's default settings with any local settings
     * this function is automatically called through getConf()
     *
     * @todo this could be made protected and be moved to the trait only
     */
    public function loadConfig();

    /**
     * Loads a given helper plugin (if enabled)
     *
     * @author  Esther Brunner <wikidesign@gmail.com>
     *
     * @param   string $name name of plugin to load
     * @param   bool $msg if a message should be displayed in case the plugin is not available
     * @return  PluginInterface|null helper plugin object
     */
    public function loadHelper($name, $msg = true);

    /**
     * email
     * standardised function to generate an email link according to obfuscation settings
     *
     * @param string $email
     * @param string $name
     * @param string $class
     * @param string $more
     * @return string html
     */
    public function email($email, $name = '', $class = '', $more = '');

    /**
     * external_link
     * standardised function to generate an external link according to conf settings
     *
     * @param string $link
     * @param string $title
     * @param string $class
     * @param string $target
     * @param string $more
     * @return string
     */
    public function external_link($link, $title = '', $class = '', $target = '', $more = '');

    /**
     * output text string through the parser, allows dokuwiki markup to be used
     * very ineffecient for small pieces of data - try not to use
     *
     * @param string $text wiki markup to parse
     * @param string $format output format
     * @return null|string
     */
    public function render_text($text, $format = 'xhtml');

    /**
     * Allow the plugin to prevent DokuWiki from reusing an instance
     *
     * @return bool   false if the plugin has to be instantiated
     */
    public function isSingleton();
}



