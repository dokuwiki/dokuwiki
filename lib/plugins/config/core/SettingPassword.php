<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_password
 */
class SettingPassword extends SettingString {

    protected $_code = 'plain';  // mechanism to be used to obscure passwords

    /**
     * update changed setting with user provided value $input
     * - if changed value fails error check, save it to $this->_input (to allow echoing later)
     * - if changed value passes error check, set $this->_local to the new value
     *
     * @param  mixed $input the new value
     * @return boolean          true if changed, false otherwise (also on error)
     */
    public function update($input) {
        if($this->is_protected()) return false;
        if(!$input) return false;

        if($this->_pattern && !preg_match($this->_pattern, $input)) {
            $this->_error = true;
            $this->_input = $input;
            return false;
        }

        $this->_local = conf_encodeString($input, $this->_code);
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

        $disable = $this->is_protected() ? 'disabled="disabled"' : '';

        $key = htmlspecialchars($this->_key);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<input id="config___' . $key . '" name="config[' . $key .
            ']" autocomplete="off" type="password" class="edit" value="" ' . $disable . ' />';
        return array($label, $input);
    }
}
