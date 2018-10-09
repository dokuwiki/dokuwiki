<?php

/**
 * Do not inherit directly from this class, instead inherit from the specialized
 * ones in lib/plugin
 */
trait DokuWiki_PluginTrait {

    protected $localised = false;        // set to true by setupLocale() after loading language dependent strings
    protected $lang = array();           // array to hold language dependent strings, best accessed via ->getLang()
    protected $configloaded = false;     // set to true by loadConfig() after loading plugin configuration variables
    protected $conf = array();           // array to hold plugin settings, best accessed via ->getConf()

    /**
     * @see DokuWiki_PluginInterface::getInfo()
     */
    public function getInfo() {
        $parts = explode('_', get_class($this));
        $info = DOKU_PLUGIN . '/' . $parts[2] . '/plugin.info.txt';
        if(file_exists($info)) return confToHash($info);

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

    /**
     * @see DokuWiki_PluginInterface::isSingleton()
     */
    public function isSingleton() {
        return true;
    }

    /**
     * @see DokuWiki_PluginInterface::loadHelper()
     */
    public function loadHelper($name, $msg = true) {
        $obj = plugin_load('helper', $name);
        if(is_null($obj) && $msg) msg("Helper plugin $name is not available or invalid.", -1);
        return $obj;
    }

    // region introspection methods

    /**
     * @see DokuWiki_PluginInterface::getPluginType()
     */
    public function getPluginType() {
        list($t) = explode('_', get_class($this), 2);
        return $t;
    }

    /**
     * @see DokuWiki_PluginInterface::getPluginName()
     */
    public function getPluginName() {
        list(/* $t */, /* $p */, $n) = explode('_', get_class($this), 4);
        return $n;
    }

    /**
     * @see DokuWiki_PluginInterface::getPluginComponent()
     */
    public function getPluginComponent() {
        list(/* $t */, /* $p */, /* $n */, $c) = explode('_', get_class($this), 4);
        return (isset($c) ? $c : '');
    }

    // endregion
    // region localization methods

    /**
     * @see DokuWiki_PluginInterface::getLang()
     */
    public function getLang($id) {
        if(!$this->localised) $this->setupLocale();

        return (isset($this->lang[$id]) ? $this->lang[$id] : '');
    }

    /**
     * @see DokuWiki_PluginInterface::locale_xhtml()
     */
    public function locale_xhtml($id) {
        return p_cached_output($this->localFN($id));
    }

    /**
     * @see DokuWiki_PluginInterface::localFN()
     */
    public function localFN($id, $ext = 'txt') {
        global $conf;
        $plugin = $this->getPluginName();
        $file = DOKU_CONF . 'plugin_lang/' . $plugin . '/' . $conf['lang'] . '/' . $id . '.' . $ext;
        if(!file_exists($file)) {
            $file = DOKU_PLUGIN . $plugin . '/lang/' . $conf['lang'] . '/' . $id . '.' . $ext;
            if(!file_exists($file)) {
                //fall back to english
                $file = DOKU_PLUGIN . $plugin . '/lang/en/' . $id . '.' . $ext;
            }
        }
        return $file;
    }

    /**
     * @see DokuWiki_PluginInterface::setupLocale()
     */
    public function setupLocale() {
        if($this->localised) return;

        global $conf, $config_cascade; // definitely don't invoke "global $lang"
        $path = DOKU_PLUGIN . $this->getPluginName() . '/lang/';

        $lang = array();

        // don't include once, in case several plugin components require the same language file
        @include($path . 'en/lang.php');
        foreach($config_cascade['lang']['plugin'] as $config_file) {
            if(file_exists($config_file . $this->getPluginName() . '/en/lang.php')) {
                include($config_file . $this->getPluginName() . '/en/lang.php');
            }
        }

        if($conf['lang'] != 'en') {
            @include($path . $conf['lang'] . '/lang.php');
            foreach($config_cascade['lang']['plugin'] as $config_file) {
                if(file_exists($config_file . $this->getPluginName() . '/' . $conf['lang'] . '/lang.php')) {
                    include($config_file . $this->getPluginName() . '/' . $conf['lang'] . '/lang.php');
                }
            }
        }

        $this->lang = $lang;
        $this->localised = true;
    }

    // endregion
    // region configuration methods

    /**
     * @see DokuWiki_PluginInterface::getConf()
     */
    public function getConf($setting, $notset = false) {

        if(!$this->configloaded) {
            $this->loadConfig();
        }

        if(isset($this->conf[$setting])) {
            return $this->conf[$setting];
        } else {
            return $notset;
        }
    }

    /**
     * @see DokuWiki_PluginInterface::loadConfig()
     */
    public function loadConfig() {
        global $conf;

        $defaults = $this->readDefaultSettings();
        $plugin = $this->getPluginName();

        foreach($defaults as $key => $value) {
            if(isset($conf['plugin'][$plugin][$key])) continue;
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

        $path = DOKU_PLUGIN . $this->getPluginName() . '/conf/';
        $conf = array();

        if(file_exists($path . 'default.php')) {
            include($path . 'default.php');
        }

        return $conf;
    }

    // endregion
    // region output methods

    /**
     * @see DokuWiki_PluginInterface::email()
     */
    public function email($email, $name = '', $class = '', $more = '') {
        if(!$email) return $name;
        $email = obfuscate($email);
        if(!$name) $name = $email;
        $class = "class='" . ($class ? $class : 'mail') . "'";
        return "<a href='mailto:$email' $class title='$email' $more>$name</a>";
    }

    /**
     * @see DokuWiki_PluginInterface::external_link()
     */
    public function external_link($link, $title = '', $class = '', $target = '', $more = '') {
        global $conf;

        $link = htmlentities($link);
        if(!$title) $title = $link;
        if(!$target) $target = $conf['target']['extern'];
        if($conf['relnofollow']) $more .= ' rel="nofollow"';

        if($class) $class = " class='$class'";
        if($target) $target = " target='$target'";
        if($more) $more = " " . trim($more);

        return "<a href='$link'$class$target$more>$title</a>";
    }

    /**
     * @see DokuWiki_PluginInterface::render_text()
     */
    public function render_text($text, $format = 'xhtml') {
        return p_render($format, p_get_instructions($text), $info);
    }

    // endregion
}
