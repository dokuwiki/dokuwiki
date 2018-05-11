<?php
/**
 * Configuration Class
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 * @author  Ben Coburn <btcoburn@silicodon.net>
 */

namespace dokuwiki\plugin\config\core;

/**
 * Class configuration
 */
class Configuration {

    const KEYMARKER = '____';

    protected $_name = 'conf';     // name of the config variable found in the files (overridden by $config['varname'])
    protected $_format = 'php';    // format of the config file, supported formats - php (overridden by $config['format'])
    protected $_heading = '';      // heading string written at top of config file - don't include comment indicators

    /** @var ConfigParser */
    protected $parser;


    protected $_loaded = false;    // set to true after configuration files are loaded
    protected $_metadata = array();// holds metadata describing the settings
    /** @var Setting[] */
    public $setting = array();  // array of setting objects
    public $locked = false;     // configuration is considered locked if it can't be updated
    public $show_disabled_plugins = false;

    // configuration filenames
    protected $_default_files = array();
    protected $_local_files = array();      // updated configuration is written to the first file
    protected $_protected_files = array();

    protected $_plugin_list = null;

    /**
     * constructor
     *
     * @param string $datafile path to config metadata file
     */
    public function __construct($datafile) {
        global $conf, $config_cascade;

        if(!file_exists($datafile)) {
            msg('No configuration metadata found at - ' . htmlspecialchars($datafile), -1);
            return;
        }
        $meta = array();
        /** @var array $config gets loaded via include here */
        include($datafile);

        if(isset($config['varname'])) $this->_name = $config['varname'];
        if(isset($config['format'])) $this->_format = $config['format'];
        if(isset($config['heading'])) $this->_heading = $config['heading'];

        $this->_default_files = $config_cascade['main']['default'];
        $this->_local_files = $config_cascade['main']['local'];
        $this->_protected_files = $config_cascade['main']['protected'];

        $this->parser = new ConfigParser($this->_name, Configuration::KEYMARKER);

        $this->locked = $this->_is_locked();
        $this->_metadata = array_merge($meta, $this->get_plugintpl_metadata($conf['template']));
        $this->retrieve_settings();
    }

    /**
     * Retrieve and stores settings in setting[] attribute
     */
    public function retrieve_settings() {
        global $conf;
        $no_default_check = array('setting_fieldset', 'setting_undefined', 'setting_no_class');

        if(!$this->_loaded) {
            $default = array_merge(
                $this->get_plugintpl_default($conf['template']),
                $this->_read_config_group($this->_default_files)
            );
            $local = $this->_read_config_group($this->_local_files);
            $protected = $this->_read_config_group($this->_protected_files);

            $keys = array_merge(
                array_keys($this->_metadata),
                array_keys($default),
                array_keys($local),
                array_keys($protected)
            );
            $keys = array_unique($keys);

            $param = null;
            foreach($keys as $key) {
                if(isset($this->_metadata[$key])) {
                    $class = $this->_metadata[$key][0];

                    if($class && class_exists('setting_' . $class)) {
                        $class = 'setting_' . $class;
                    } else {
                        if($class != '') {
                            $this->setting[] = new SettingNoClass($key, $param);
                        }
                        $class = 'setting';
                    }

                    $param = $this->_metadata[$key];
                    array_shift($param);
                } else {
                    $class = 'setting_undefined';
                    $param = null;
                }

                if(!in_array($class, $no_default_check) && !isset($default[$key])) {
                    $this->setting[] = new SettingNoDefault($key, $param);
                }

                $this->setting[$key] = new $class($key, $param);

                $d = array_key_exists($key, $default) ? $default[$key] : null;
                $l = array_key_exists($key, $local) ? $local[$key] : null;
                $p = array_key_exists($key, $protected) ? $protected[$key] : null;

                $this->setting[$key]->initialize($d, $l, $p);
            }

            $this->_loaded = true;
        }
    }

    /**
     * Stores setting[] array to file
     *
     * @param string $id Name of plugin, which saves the settings
     * @param string $header Text at the top of the rewritten settings file
     * @param bool $backup backup current file? (remove any existing backup)
     * @return bool succesful?
     */
    public function save_settings($id, $header = '', $backup = true) {
        global $conf;

        if($this->locked) return false;

        // write back to the last file in the local config cascade
        $file = end($this->_local_files);

        // backup current file (remove any existing backup)
        if(file_exists($file) && $backup) {
            if(file_exists($file . '.bak')) @unlink($file . '.bak');
            if(!io_rename($file, $file . '.bak')) return false;
        }

        if(!$fh = @fopen($file, 'wb')) {
            io_rename($file . '.bak', $file);     // problem opening, restore the backup
            return false;
        }

        if(empty($header)) $header = $this->_heading;

        $out = $this->_out_header($id, $header);

        foreach($this->setting as $setting) {
            $out .= $setting->out($this->_name, $this->_format);
        }

        $out .= $this->_out_footer();

        @fwrite($fh, $out);
        fclose($fh);
        if($conf['fperm']) chmod($file, $conf['fperm']);
        return true;
    }

    /**
     * Update last modified time stamp of the config file
     *
     * @return bool
     */
    public function touch_settings() {
        if($this->locked) return false;
        $file = end($this->_local_files);
        return @touch($file);
    }

    /**
     * Read and merge given config files
     *
     * @param array $files file paths
     * @return array config settings
     */
    protected function _read_config_group($files) {
        $config = array();
        foreach($files as $file) {
            $config = array_merge($config, $this->parser->parse($file));
        }

        return $config;
    }


    /**
     * Returns header of rewritten settings file
     *
     * @param string $id plugin name of which generated this output
     * @param string $header additional text for at top of the file
     * @return string text of header
     */
    protected function _out_header($id, $header) {
        $out = '';
        if($this->_format == 'php') {
            $out .= '<' . '?php' . "\n" .
                "/*\n" .
                " * " . $header . "\n" .
                " * Auto-generated by " . $id . " plugin\n" .
                " * Run for user: " . $_SERVER['REMOTE_USER'] . "\n" .
                " * Date: " . date('r') . "\n" .
                " */\n\n";
        }

        return $out;
    }

    /**
     * Returns footer of rewritten settings file
     *
     * @return string text of footer
     */
    protected function _out_footer() {
        $out = '';
        if($this->_format == 'php') {
            $out .= "\n// end auto-generated content\n";
        }

        return $out;
    }

    /**
     * Configuration is considered locked if there is no local settings filename
     * or the directory its in is not writable or the file exists and is not writable
     *
     * @return bool true: locked, false: writable
     */
    protected function _is_locked() {
        if(!$this->_local_files) return true;

        $local = $this->_local_files[0];

        if(!is_writable(dirname($local))) return true;
        if(file_exists($local) && !is_writable($local)) return true;

        return false;
    }

    /**
     * not used ... conf's contents are an array!
     * reduce any multidimensional settings to one dimension using Configuration::KEYMARKER
     *
     * @param $conf
     * @param string $prefix
     * @return array
     */
    protected function _flatten($conf, $prefix = '') {

        $out = array();

        foreach($conf as $key => $value) {
            if(!is_array($value)) {
                $out[$prefix . $key] = $value;
                continue;
            }

            $tmp = $this->_flatten($value, $prefix . $key . Configuration::KEYMARKER);
            $out = array_merge($out, $tmp);
        }

        return $out;
    }

    /**
     * Returns array of plugin names
     *
     * @return array plugin names
     * @triggers PLUGIN_CONFIG_PLUGINLIST event
     */
    protected function get_plugin_list() {
        if(is_null($this->_plugin_list)) {
            $list = plugin_list('', $this->show_disabled_plugins);

            // remove this plugin from the list
            $idx = array_search('config', $list);
            unset($list[$idx]);

            trigger_event('PLUGIN_CONFIG_PLUGINLIST', $list);
            $this->_plugin_list = $list;
        }

        return $this->_plugin_list;
    }

    /**
     * load metadata for plugin and template settings
     *
     * @param string $tpl name of active template
     * @return array metadata of settings
     */
    protected function get_plugintpl_metadata($tpl) {
        $file = '/conf/metadata.php';
        $class = '/conf/settings.class.php';
        $metadata = array();

        foreach($this->get_plugin_list() as $plugin) {
            $plugin_dir = plugin_directory($plugin);
            if(file_exists(DOKU_PLUGIN . $plugin_dir . $file)) {
                $meta = array();
                @include(DOKU_PLUGIN . $plugin_dir . $file);
                @include(DOKU_PLUGIN . $plugin_dir . $class);
                if(!empty($meta)) {
                    $metadata['plugin' . Configuration::KEYMARKER . $plugin . Configuration::KEYMARKER . 'plugin_settings_name'] = ['fieldset'];
                }
                foreach($meta as $key => $value) {
                    if($value[0] == 'fieldset') {
                        continue;
                    } //plugins only get one fieldset
                    $metadata['plugin' . Configuration::KEYMARKER . $plugin . Configuration::KEYMARKER . $key] = $value;
                }
            }
        }

        // the same for the active template
        if(file_exists(tpl_incdir() . $file)) {
            $meta = array();
            @include(tpl_incdir() . $file);
            @include(tpl_incdir() . $class);
            if(!empty($meta)) {
                $metadata['tpl' . Configuration::KEYMARKER . $tpl . Configuration::KEYMARKER . 'template_settings_name'] = array('fieldset');
            }
            foreach($meta as $key => $value) {
                if($value[0] == 'fieldset') {
                    continue;
                } //template only gets one fieldset
                $metadata['tpl' . Configuration::KEYMARKER . $tpl . Configuration::KEYMARKER . $key] = $value;
            }
        }

        return $metadata;
    }

    /**
     * Load default settings for plugins and templates
     *
     * @param string $tpl name of active template
     * @return array default settings
     */
    protected function get_plugintpl_default($tpl) {
        $file = '/conf/default.php';
        $default = array();

        foreach($this->get_plugin_list() as $plugin) {
            $plugin_dir = plugin_directory($plugin);
            if(file_exists(DOKU_PLUGIN . $plugin_dir . $file)) {
                $conf = $this->parser->parse(DOKU_PLUGIN . $plugin_dir . $file);
                foreach($conf as $key => $value) {
                    $default['plugin' . Configuration::KEYMARKER . $plugin . Configuration::KEYMARKER . $key] = $value;
                }
            }
        }

        // the same for the active template
        if(file_exists(tpl_incdir() . $file)) {
            $conf = $this->parser->parse(tpl_incdir() . $file);
            foreach($conf as $key => $value) {
                $default['tpl' . Configuration::KEYMARKER . $tpl . Configuration::KEYMARKER . $key] = $value;
            }
        }

        return $default;
    }

}

