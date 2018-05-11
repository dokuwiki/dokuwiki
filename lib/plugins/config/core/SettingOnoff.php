<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_onoff
 */
class SettingOnoff extends SettingNumeric {
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
            $disable = ' disabled="disabled"';
        } else {
            $value = is_null($this->_local) ? $this->_default : $this->_local;
        }

        $key = htmlspecialchars($this->_key);
        $checked = ($value) ? ' checked="checked"' : '';

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<div class="input"><input id="config___' . $key . '" name="config[' . $key .
            ']" type="checkbox" class="checkbox" value="1"' . $checked . $disable . '/></div>';
        return array($label, $input);
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
        if($this->is_protected()) return false;

        $input = ($input) ? 1 : 0;
        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;

        $this->_local = $input;
        return true;
    }
}
