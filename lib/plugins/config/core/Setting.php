<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting
 */
class Setting {

    protected $_key = '';
    protected $_default = null;
    protected $_local = null;
    protected $_protected = null;

    protected $_pattern = '';
    protected $_error = false;            // only used by those classes which error check
    protected $_input = null;             // only used by those classes which error check
    protected $_caution = null;           // used by any setting to provide an alert along with the setting
    // valid alerts, 'warning', 'danger', 'security'
    // images matching the alerts are in the plugin's images directory

    static protected $_validCautions = array('warning', 'danger', 'security');

    /**
     * @param string $key
     * @param array|null $params array with metadata of setting
     */
    public function __construct($key, $params = null) {
        $this->_key = $key;

        if(is_array($params)) {
            foreach($params as $property => $value) {
                $this->$property = $value;
            }
        }
    }

    /**
     * Receives current values for the setting $key
     *
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default, $local, $protected) {
        if(isset($default)) $this->_default = $default;
        if(isset($local)) $this->_local = $local;
        if(isset($protected)) $this->_protected = $protected;
    }

    /**
     * Should this type of config have a default?
     *
     * @return bool
     */
    public function shouldHaveDefault() {
        return true;
    }

    /**
     * update changed setting with user provided value $input
     * - if changed value fails error check, save it to $this->_input (to allow echoing later)
     * - if changed value passes error check, set $this->_local to the new value
     *
     * @param  mixed $input the new value
     * @return boolean          true if changed, false otherwise (also on error)
     */
    public function update($input) {
        if(is_null($input)) return false;
        if($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;

        if($this->_pattern && !preg_match($this->_pattern, $input)) {
            $this->_error = true;
            $this->_input = $input;
            return false;
        }

        $this->_local = $input;
        return true;
    }

    /**
     * Build html for label and input of setting
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @param bool $echo true: show inputted value, when error occurred, otherwise the stored setting
     * @return string[] with content array(string $label_html, string $input_html)
     */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        $disable = '';

        if($this->is_protected()) {
            $value = $this->_protected;
            $disable = 'disabled="disabled"';
        } else {
            if($echo && $this->_error) {
                $value = $this->_input;
            } else {
                $value = is_null($this->_local) ? $this->_default : $this->_local;
            }
        }

        $key = htmlspecialchars($this->_key);
        $value = formText($value);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<textarea rows="3" cols="40" id="config___' . $key .
            '" name="config[' . $key . ']" class="edit" ' . $disable . '>' . $value . '</textarea>';
        return array($label, $input);
    }

    /**
     * Generate string to save setting value to file according to $fmt
     *
     * @param string $var name of variable
     * @param string $fmt save format
     * @return string
     */
    public function out($var, $fmt = 'php') {

        if($this->is_protected()) return '';
        if(is_null($this->_local) || ($this->_default == $this->_local)) return '';

        $out = '';

        if($fmt == 'php') {
            $tr = array("\\" => '\\\\', "'" => '\\\'');

            $out = '$' . $var . "['" . $this->_out_key() . "'] = '" . strtr(cleanText($this->_local), $tr) . "';\n";
        }

        return $out;
    }

    /**
     * Returns the localized prompt
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @return string text
     */
    public function prompt(\admin_plugin_config $plugin) {
        $prompt = $plugin->getLang($this->_key);
        if(!$prompt) $prompt = htmlspecialchars(str_replace(array('____', '_'), ' ', $this->_key));
        return $prompt;
    }

    /**
     * Is setting protected
     *
     * @return bool
     */
    public function is_protected() {
        return !is_null($this->_protected);
    }

    /**
     * Is setting the default?
     *
     * @return bool
     */
    public function is_default() {
        return !$this->is_protected() && is_null($this->_local);
    }

    /**
     * Has an error?
     *
     * @return bool
     */
    public function error() {
        return $this->_error;
    }

    /**
     * Returns caution
     *
     * @return false|string caution string, otherwise false for invalid caution
     */
    public function caution() {
        if(!empty($this->_caution)) {
            if(!in_array($this->_caution, Setting::$_validCautions)) {
                trigger_error(
                    'Invalid caution string (' . $this->_caution . ') in metadata for setting "' . $this->_key . '"',
                    E_USER_WARNING
                );
                return false;
            }
            return $this->_caution;
        }
        // compatibility with previous cautionList
        // TODO: check if any plugins use; remove
        if(!empty($this->_cautionList[$this->_key])) {
            $this->_caution = $this->_cautionList[$this->_key];
            unset($this->_cautionList);

            return $this->caution();
        }
        return false;
    }

    /**
     * Returns setting key, eventually with referer to config: namespace at dokuwiki.org
     *
     * @param bool $pretty create nice key
     * @param bool $url provide url to config: namespace
     * @return string key
     */
    public function _out_key($pretty = false, $url = false) {
        if($pretty) {
            $out = str_replace(Configuration::KEYMARKER, "»", $this->_key);
            if($url && !strstr($out, '»')) {//provide no urls for plugins, etc.
                if($out == 'start') //one exception
                    return '<a href="http://www.dokuwiki.org/config:startpage">' . $out . '</a>';
                else
                    return '<a href="http://www.dokuwiki.org/config:' . $out . '">' . $out . '</a>';
            }
            return $out;
        } else {
            return str_replace(Configuration::KEYMARKER, "']['", $this->_key);
        }
    }
}
