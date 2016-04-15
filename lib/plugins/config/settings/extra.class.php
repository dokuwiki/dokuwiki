<?php
/**
 * additional setting classes specific to these settings
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */

if (!class_exists('setting_sepchar')) {
    /**
     * Class setting_sepchar
     */
    class setting_sepchar extends setting_multichoice {

        /**
         * @param string $key
         * @param array|null $param array with metadata of setting
         */
        function __construct($key,$param=null) {
            $str = '_-.';
            for ($i=0;$i<strlen($str);$i++) $this->_choices[] = $str{$i};

            // call foundation class constructor
            parent::__construct($key,$param);
        }
    }
}

if (!class_exists('setting_savedir')) {
    /**
     * Class setting_savedir
     */
    class setting_savedir extends setting_string {

        /**
         * update changed setting with user provided value $input
         * - if changed value fails error check, save it to $this->_input (to allow echoing later)
         * - if changed value passes error check, set $this->_local to the new value
         *
         * @param  mixed   $input   the new value
         * @return boolean          true if changed, false otherwise (also on error)
         */
        function update($input) {
            if ($this->is_protected()) return false;

            $value = is_null($this->_local) ? $this->_default : $this->_local;
            if ($value == $input) return false;

            if (!init_path($input)) {
                $this->_error = true;
                $this->_input = $input;
                return false;
            }

            $this->_local = $input;
            return true;
        }
    }
}

if (!class_exists('setting_authtype')) {
    /**
     * Class setting_authtype
     */
    class setting_authtype extends setting_multichoice {

        /**
         * Receives current values for the setting $key
         *
         * @param mixed $default   default setting value
         * @param mixed $local     local setting value
         * @param mixed $protected protected setting value
         */
        function initialize($default,$local,$protected) {
            /** @var $plugin_controller Doku_Plugin_Controller */
            global $plugin_controller;

            // retrieve auth types provided by plugins
            foreach ($plugin_controller->getList('auth') as $plugin) {
                $this->_choices[] = $plugin;
            }

            parent::initialize($default,$local,$protected);
        }

        /**
         * update changed setting with user provided value $input
         * - if changed value fails error check, save it to $this->_input (to allow echoing later)
         * - if changed value passes error check, set $this->_local to the new value
         *
         * @param  mixed   $input   the new value
         * @return boolean          true if changed, false otherwise (also on error)
         */
        function update($input) {
            /** @var $plugin_controller Doku_Plugin_Controller */
            global $plugin_controller;

            // is an update possible/requested?
            $local = $this->_local;                       // save this, parent::update() may change it
            if (!parent::update($input)) return false;    // nothing changed or an error caught by parent
            $this->_local = $local;                       // restore original, more error checking to come

            // attempt to load the plugin
            $auth_plugin = $plugin_controller->load('auth', $input);

            // @TODO: throw an error in plugin controller instead of returning null
            if (is_null($auth_plugin)) {
                $this->_error = true;
                msg('Cannot load Auth Plugin "' . $input . '"', -1);
                return false;
            }

            // verify proper instantiation (is this really a plugin?) @TODO use instanceof? implement interface?
            if (is_object($auth_plugin) && !method_exists($auth_plugin, 'getPluginName')) {
                $this->_error = true;
                msg('Cannot create Auth Plugin "' . $input . '"', -1);
                return false;
            }

            // did we change the auth type? logout
            global $conf;
            if($conf['authtype'] != $input) {
                msg('Authentication system changed. Please re-login.');
                auth_logoff();
            }

            $this->_local = $input;
            return true;
        }
    }
}

if (!class_exists('setting_im_convert')) {
    /**
     * Class setting_im_convert
     */
    class setting_im_convert extends setting_string {

        /**
         * update changed setting with user provided value $input
         * - if changed value fails error check, save it to $this->_input (to allow echoing later)
         * - if changed value passes error check, set $this->_local to the new value
         *
         * @param  mixed   $input   the new value
         * @return boolean          true if changed, false otherwise (also on error)
         */
        function update($input) {
            if ($this->is_protected()) return false;

            $input = trim($input);

            $value = is_null($this->_local) ? $this->_default : $this->_local;
            if ($value == $input) return false;

            if ($input && !file_exists($input)) {
                $this->_error = true;
                $this->_input = $input;
                return false;
            }

            $this->_local = $input;
            return true;
        }
    }
}

if (!class_exists('setting_disableactions')) {
    /**
     * Class setting_disableactions
     */
    class setting_disableactions extends setting_multicheckbox {

        /**
         * Build html for label and input of setting
         *
         * @param DokuWiki_Plugin $plugin object of config plugin
         * @param bool            $echo   true: show inputted value, when error occurred, otherwise the stored setting
         * @return array with content array(string $label_html, string $input_html)
         */
        function html(&$plugin, $echo=false) {
            global $lang;

            // make some language adjustments (there must be a better way)
            // transfer some DokuWiki language strings to the plugin
            if (!$plugin->localised) $plugin->setupLocale();
            $plugin->lang[$this->_key.'_revisions'] = $lang['btn_revs'];

            foreach ($this->_choices as $choice)
              if (isset($lang['btn_'.$choice])) $plugin->lang[$this->_key.'_'.$choice] = $lang['btn_'.$choice];

            return parent::html($plugin, $echo);
        }
    }
}

if (!class_exists('setting_compression')) {
    /**
     * Class setting_compression
     */
    class setting_compression extends setting_multichoice {

        var $_choices = array('0');      // 0 = no compression, always supported

        /**
         * Receives current values for the setting $key
         *
         * @param mixed $default   default setting value
         * @param mixed $local     local setting value
         * @param mixed $protected protected setting value
         */
        function initialize($default,$local,$protected) {

            // populate _choices with the compression methods supported by this php installation
            if (function_exists('gzopen')) $this->_choices[] = 'gz';
            if (function_exists('bzopen')) $this->_choices[] = 'bz2';

            parent::initialize($default,$local,$protected);
        }
    }
}

if (!class_exists('setting_license')) {
    /**
     * Class setting_license
     */
    class setting_license extends setting_multichoice {

        var $_choices = array('');      // none choosen

        /**
         * Receives current values for the setting $key
         *
         * @param mixed $default   default setting value
         * @param mixed $local     local setting value
         * @param mixed $protected protected setting value
         */
        function initialize($default,$local,$protected) {
            global $license;

            foreach($license as $key => $data){
                $this->_choices[] = $key;
                $this->lang[$this->_key.'_o_'.$key] = $data['name']; // stored in setting
            }

            parent::initialize($default,$local,$protected);
        }
    }
}


if (!class_exists('setting_renderer')) {
    /**
     * Class setting_renderer
     */
    class setting_renderer extends setting_multichoice {
        var $_prompts = array();
        var $_format = null;

        /**
         * Receives current values for the setting $key
         *
         * @param mixed $default   default setting value
         * @param mixed $local     local setting value
         * @param mixed $protected protected setting value
         */
        function initialize($default,$local,$protected) {
            $format = $this->_format;

            foreach (plugin_list('renderer') as $plugin) {
                $renderer = plugin_load('renderer',$plugin);
                if (method_exists($renderer,'canRender') && $renderer->canRender($format)) {
                    $this->_choices[] = $plugin;

                    $info = $renderer->getInfo();
                    $this->_prompts[$plugin] = $info['name'];
                }
            }

            parent::initialize($default,$local,$protected);
        }

        /**
         * Build html for label and input of setting
         *
         * @param DokuWiki_Plugin $plugin object of config plugin
         * @param bool            $echo   true: show inputted value, when error occurred, otherwise the stored setting
         * @return array with content array(string $label_html, string $input_html)
         */
        function html(&$plugin, $echo=false) {

            // make some language adjustments (there must be a better way)
            // transfer some plugin names to the config plugin
            if (!$plugin->localised) $plugin->setupLocale();

            foreach ($this->_choices as $choice) {
                if (!isset($plugin->lang[$this->_key.'_o_'.$choice])) {
                    if (!isset($this->_prompts[$choice])) {
                        $plugin->lang[$this->_key.'_o_'.$choice] = sprintf($plugin->lang['renderer__core'],$choice);
                    } else {
                        $plugin->lang[$this->_key.'_o_'.$choice] = sprintf($plugin->lang['renderer__plugin'],$this->_prompts[$choice]);
                    }
                }
            }
            return parent::html($plugin, $echo);
        }
    }
}
