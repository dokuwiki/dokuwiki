<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_string
 */
class SettingString extends Setting {
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
        $value = htmlspecialchars($value);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<input id="config___' . $key . '" name="config[' . $key .
            ']" type="text" class="edit" value="' . $value . '" ' . $disable . '/>';
        return array($label, $input);
    }
}
