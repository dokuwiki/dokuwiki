<?php

namespace dokuwiki\plugin\config\core\Setting;

use dokuwiki\plugin\config\core\Configuration;

/**
 * Class Setting
 */
class Setting {
    /** @var string unique identifier of this setting */
    protected $key = '';

    /** @var mixed the default value of this setting */
    protected $default = null;
    /** @var mixed the local value of this setting */
    protected $local = null;
    /** @var mixed the protected value of this setting */
    protected $protected = null;

    /** @var array valid alerts, images matching the alerts are in the plugin's images directory */
    static protected $validCautions = array('warning', 'danger', 'security');

    protected $pattern = '';
    protected $error = false;            // only used by those classes which error check
    protected $input = null;             // only used by those classes which error check
    protected $caution = null;           // used by any setting to provide an alert along with the setting

    /**
     * Constructor.
     *
     * The given parameters will be set up as class properties
     *
     * @see initialize() to set the actual value of the setting
     *
     * @param string $key
     * @param array|null $params array with metadata of setting
     */
    public function __construct($key, $params = null) {
        $this->key = $key;

        if(is_array($params)) {
            foreach($params as $property => $value) {
                $property = trim($property, '_'); // we don't use underscores anymore
                $this->$property = $value;
            }
        }
    }

    /**
     * Set the current values for the setting $key
     *
     * This is used to initialize the setting with the data read form the config files.
     *
     * @see update() to set a new value
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default = null, $local = null, $protected = null) {
        $this->default = $this->cleanValue($default);
        $this->local = $this->cleanValue($local);
        $this->protected = $this->cleanValue($protected);
    }

    /**
     * update changed setting with validated user provided value $input
     * - if changed value fails validation check, save it to $this->input (to allow echoing later)
     * - if changed value passes validation check, set $this->local to the new value
     *
     * @param  mixed $input the new value
     * @return boolean          true if changed, false otherwise
     */
    public function update($input) {
        if(is_null($input)) return false;
        if($this->isProtected()) return false;
        $input = $this->cleanValue($input);

        $value = is_null($this->local) ? $this->default : $this->local;
        if($value == $input) return false;

        // validate new value
        if($this->pattern && !preg_match($this->pattern, $input)) {
            $this->error = true;
            $this->input = $input;
            return false;
        }

        // update local copy of this setting with new value
        $this->local = $input;

        // setting ready for update
        return true;
    }

    /**
     * Clean a value read from a config before using it internally
     *
     * Default implementation returns $value as is. Subclasses can override.
     * Note: null should always be returned as null!
     *
     * This is applied in initialize() and update()
     *
     * @param mixed $value
     * @return mixed
     */
    protected function cleanValue($value) {
        return $value;
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
     * Get this setting's unique key
     *
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Get the key of this setting marked up human readable
     *
     * @param bool $url link to dokuwiki.org manual?
     * @return string
     */
    public function getPrettyKey($url = true) {
        $out = str_replace(Configuration::KEYMARKER, "»", $this->key);
        if($url && !strstr($out, '»')) {//provide no urls for plugins, etc.
            if($out == 'start') {
                // exception, because this config name is clashing with our actual start page
                return '<a href="http://www.dokuwiki.org/config:startpage">' . $out . '</a>';
            } else {
                return '<a href="http://www.dokuwiki.org/config:' . $out . '">' . $out . '</a>';
            }
        }
        return $out;
    }

    /**
     * Returns setting key as an array key separator
     *
     * This is used to create form output
     *
     * @return string key
     */
    public function getArrayKey() {
        return str_replace(Configuration::KEYMARKER, "']['", $this->key);
    }

    /**
     * What type of configuration is this
     *
     * Returns one of
     *
     * 'plugin' for plugin configuration
     * 'template' for template configuration
     * 'dokuwiki' for core configuration
     *
     * @return string
     */
    public function getType() {
        if(substr($this->getKey(), 0, 10) == 'plugin' . Configuration::KEYMARKER) {
            return 'plugin';
        } else if(substr($this->getKey(), 0, 7) == 'tpl' . Configuration::KEYMARKER) {
            return 'template';
        } else {
            return 'dokuwiki';
        }
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

        if($this->isProtected()) {
            $value = $this->protected;
            $disable = 'disabled="disabled"';
        } else {
            if($echo && $this->error) {
                $value = $this->input;
            } else {
                $value = is_null($this->local) ? $this->default : $this->local;
            }
        }

        $key = htmlspecialchars($this->key);
        $value = formText($value);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<textarea rows="3" cols="40" id="config___' . $key .
            '" name="config[' . $key . ']" class="edit" ' . $disable . '>' . $value . '</textarea>';
        return array($label, $input);
    }

    /**
     * Should the current local value be saved?
     *
     * @see out() to run when this returns true
     * @return bool
     */
    public function shouldBeSaved() {
        if($this->isProtected()) return false;
        if($this->local === null) return false;
        if($this->default == $this->local) return false;
        return true;
    }

    /**
     * Generate string to save local setting value to file according to $fmt
     *
     * @see shouldBeSaved() to check if this should be called
     * @param string $var name of variable
     * @param string $fmt save format
     * @return string
     */
    public function out($var, $fmt = 'php') {
        if($fmt != 'php') return '';

        $tr = array("\\" => '\\\\', "'" => '\\\''); // escape the value
        $out = '$' . $var . "['" . $this->getArrayKey() . "'] = '" . strtr(cleanText($this->local), $tr) . "';\n";

        return $out;
    }

    /**
     * Returns the localized prompt
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @return string text
     */
    public function prompt(\admin_plugin_config $plugin) {
        $prompt = $plugin->getLang($this->key);
        if(!$prompt) $prompt = htmlspecialchars(str_replace(array('____', '_'), ' ', $this->key));
        return $prompt;
    }

    /**
     * Is setting protected
     *
     * @return bool
     */
    public function isProtected() {
        return !is_null($this->protected);
    }

    /**
     * Is setting the default?
     *
     * @return bool
     */
    public function isDefault() {
        return !$this->isProtected() && is_null($this->local);
    }

    /**
     * Has an error?
     *
     * @return bool
     */
    public function hasError() {
        return $this->error;
    }

    /**
     * Returns caution
     *
     * @return false|string caution string, otherwise false for invalid caution
     */
    public function caution() {
        if(empty($this->caution)) return false;
        if(!in_array($this->caution, Setting::$validCautions)) {
            throw new \RuntimeException(
                'Invalid caution string (' . $this->caution . ') in metadata for setting "' . $this->key . '"'
            );
        }
        return $this->caution;
    }

}
