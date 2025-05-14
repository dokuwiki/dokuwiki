<?php

namespace dokuwiki\Extension;

use dokuwiki\Logger;

/**
 * Provides standard DokuWiki plugin behaviour
 */
trait PluginTrait
{
    protected $localised = false;        // set to true by setupLocale() after loading language dependent strings
    protected $lang = [];           // array to hold language dependent strings, best accessed via ->getLang()
    protected $configloaded = false;     // set to true by loadConfig() after loading plugin configuration variables
    protected $conf = [];           // array to hold plugin settings, best accessed via ->getConf()

    /**
     * @see PluginInterface::getInfo()
     */
    public function getInfo()
    {
        $class = get_class($this);
        $parts = sexplode('_', $class, 3);
        $ext = $parts[2];

        if (empty($ext)) {
            throw new \RuntimeException('Class does not follow the plugin naming convention');
        }

        // class like action_plugin_myplugin_ajax belongs to plugin 'myplugin'
        $ext = strtok($ext, '_');

        $base = [
            'base' => $ext,
            'author' => 'Unknown',
            'email' => 'unknown@example.com',
            'date' => '0000-00-00',
            'name' => $ext . ' plugin',
            'desc' => 'Unknown purpose - bad plugin.info.txt',
            'url' => 'https://www.dokuwiki.org/plugins/' . $ext,
        ];

        $file = DOKU_PLUGIN . '/' . $ext . '/plugin.info.txt';
        if (file_exists($file)) {
            $raw = confToHash($file);

            // check if all required fields are present
            $msg = 'Extension %s does not provide a valid %s in %s';
            foreach (array_keys($base) as $line) {
                if (empty($raw[$line])) Logger::error(sprintf($msg, $ext, $line, $file));
            }

            return array_merge($base, $raw);
        }

        Logger::error(sprintf('Extension %s does not provide a plugin.info.txt in %s', $ext, $file));
        return $base;
    }

    /**
     * @see PluginInterface::isSingleton()
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * @see PluginInterface::loadHelper()
     */
    public function loadHelper($name, $msg = true)
    {
        $obj = plugin_load('helper', $name);
        if (is_null($obj) && $msg) msg("Helper plugin $name is not available or invalid.", -1);
        return $obj;
    }

    // region introspection methods

    /**
     * @see PluginInterface::getPluginType()
     */
    public function getPluginType()
    {
        [$t] = explode('_', get_class($this), 2);
        return $t;
    }

    /**
     * @see PluginInterface::getPluginName()
     */
    public function getPluginName()
    {
        [/* t */, /* p */, $n] = sexplode('_', get_class($this), 4, '');
        return $n;
    }

    /**
     * @see PluginInterface::getPluginComponent()
     */
    public function getPluginComponent()
    {
        [/* t */, /* p */, /* n */, $c] = sexplode('_', get_class($this), 4, '');
        return $c;
    }

    // endregion
    // region localization methods

    /**
     * @see PluginInterface::getLang()
     */
    public function getLang($id)
    {
        if (!$this->localised) $this->setupLocale();

        return ($this->lang[$id] ?? '');
    }

    /**
     * @see PluginInterface::locale_xhtml()
     */
    public function locale_xhtml($id)
    {
        return p_cached_output($this->localFN($id));
    }

    /**
     * @see PluginInterface::localFN()
     */
    public function localFN($id, $ext = 'txt')
    {
        global $conf;
        $plugin = $this->getPluginName();
        $file = DOKU_CONF . 'plugin_lang/' . $plugin . '/' . $conf['lang'] . '/' . $id . '.' . $ext;
        if (!file_exists($file)) {
            $file = DOKU_PLUGIN . $plugin . '/lang/' . $conf['lang'] . '/' . $id . '.' . $ext;
            if (!file_exists($file)) {
                //fall back to english
                $file = DOKU_PLUGIN . $plugin . '/lang/en/' . $id . '.' . $ext;
            }
        }
        return $file;
    }

    /**
     * @see PluginInterface::setupLocale()
     */
    public function setupLocale()
    {
        if ($this->localised) return;

        global $conf, $config_cascade; // definitely don't invoke "global $lang"
        $path = DOKU_PLUGIN . $this->getPluginName() . '/lang/';

        $lang = [];

        // don't include once, in case several plugin components require the same language file
        @include($path . 'en/lang.php');
        foreach ($config_cascade['lang']['plugin'] as $config_file) {
            if (file_exists($config_file . $this->getPluginName() . '/en/lang.php')) {
                include($config_file . $this->getPluginName() . '/en/lang.php');
            }
        }

        if ($conf['lang'] != 'en') {
            @include($path . $conf['lang'] . '/lang.php');
            foreach ($config_cascade['lang']['plugin'] as $config_file) {
                if (file_exists($config_file . $this->getPluginName() . '/' . $conf['lang'] . '/lang.php')) {
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
     * @see PluginInterface::getConf()
     */
    public function getConf($setting, $notset = false)
    {

        if (!$this->configloaded) {
            $this->loadConfig();
        }

        if (isset($this->conf[$setting])) {
            return $this->conf[$setting];
        } else {
            return $notset;
        }
    }

    /**
     * @see PluginInterface::loadConfig()
     */
    public function loadConfig()
    {
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
    protected function readDefaultSettings()
    {

        $path = DOKU_PLUGIN . $this->getPluginName() . '/conf/';
        $conf = [];

        if (file_exists($path . 'default.php')) {
            include($path . 'default.php');
        }

        return $conf;
    }

    // endregion
    // region output methods

    /**
     * @see PluginInterface::email()
     */
    public function email($email, $name = '', $class = '', $more = '')
    {
        if (!$email) return $name;
        $email = obfuscate($email);
        if (!$name) $name = $email;
        $class = "class='" . ($class ?: 'mail') . "'";
        return "<a href='mailto:$email' $class title='$email' $more>$name</a>";
    }

    /**
     * @see PluginInterface::external_link()
     */
    public function external_link($link, $title = '', $class = '', $target = '', $more = '')
    {
        global $conf;

        $link = htmlentities($link);
        if (!$title) $title = $link;
        if (!$target) $target = $conf['target']['extern'];
        if ($conf['relnofollow']) $more .= ' rel="nofollow"';

        if ($class) $class = " class='$class'";
        if ($target) $target = " target='$target'";
        if ($more) $more = " " . trim($more);

        return "<a href='$link'$class$target$more>$title</a>";
    }

    /**
     * @see PluginInterface::render_text()
     */
    public function render_text($text, $format = 'xhtml')
    {
        return p_render($format, p_get_instructions($text), $info);
    }

    // endregion
}
