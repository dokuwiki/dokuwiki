<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_multichoice
 */
class SettingMultichoice extends SettingString {
    protected $_choices = array();
    public $lang; //some custom language strings are stored in setting

    /**
     * Build html for label and input of setting
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @param bool $echo true: show inputted value, when error occurred, otherwise the stored setting
     * @return string[] with content array(string $label_html, string $input_html)
     */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        $disable = '';
        $nochoice = '';

        if($this->is_protected()) {
            $value = $this->_protected;
            $disable = ' disabled="disabled"';
        } else {
            $value = is_null($this->_local) ? $this->_default : $this->_local;
        }

        // ensure current value is included
        if(!in_array($value, $this->_choices)) {
            $this->_choices[] = $value;
        }
        // disable if no other choices
        if(!$this->is_protected() && count($this->_choices) <= 1) {
            $disable = ' disabled="disabled"';
            $nochoice = $plugin->getLang('nochoice');
        }

        $key = htmlspecialchars($this->_key);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';

        $input = "<div class=\"input\">\n";
        $input .= '<select class="edit" id="config___' . $key . '" name="config[' . $key . ']"' . $disable . '>' . "\n";
        foreach($this->_choices as $choice) {
            $selected = ($value == $choice) ? ' selected="selected"' : '';
            $option = $plugin->getLang($this->_key . '_o_' . $choice);
            if(!$option && isset($this->lang[$this->_key . '_o_' . $choice])) {
                $option = $this->lang[$this->_key . '_o_' . $choice];
            }
            if(!$option) $option = $choice;

            $choice = htmlspecialchars($choice);
            $option = htmlspecialchars($option);
            $input .= '  <option value="' . $choice . '"' . $selected . ' >' . $option . '</option>' . "\n";
        }
        $input .= "</select> $nochoice \n";
        $input .= "</div>\n";

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
        if(is_null($input)) return false;
        if($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;

        if(!in_array($input, $this->_choices)) return false;

        $this->_local = $input;
        return true;
    }
}
