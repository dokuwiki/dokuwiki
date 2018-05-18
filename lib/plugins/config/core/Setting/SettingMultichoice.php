<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_multichoice
 */
class SettingMultichoice extends SettingString {
    protected $choices = array();
    public $lang; //some custom language strings are stored in setting

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        $disable = '';
        $nochoice = '';

        if($this->isProtected()) {
            $value = $this->protected;
            $disable = ' disabled="disabled"';
        } else {
            $value = is_null($this->local) ? $this->default : $this->local;
        }

        // ensure current value is included
        if(!in_array($value, $this->choices)) {
            $this->choices[] = $value;
        }
        // disable if no other choices
        if(!$this->isProtected() && count($this->choices) <= 1) {
            $disable = ' disabled="disabled"';
            $nochoice = $plugin->getLang('nochoice');
        }

        $key = htmlspecialchars($this->key);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';

        $input = "<div class=\"input\">\n";
        $input .= '<select class="edit" id="config___' . $key . '" name="config[' . $key . ']"' . $disable . '>' . "\n";
        foreach($this->choices as $choice) {
            $selected = ($value == $choice) ? ' selected="selected"' : '';
            $option = $plugin->getLang($this->key . '_o_' . $choice);
            if(!$option && isset($this->lang[$this->key . '_o_' . $choice])) {
                $option = $this->lang[$this->key . '_o_' . $choice];
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

    /** @inheritdoc */
    public function update($input) {
        if(is_null($input)) return false;
        if($this->isProtected()) return false;

        $value = is_null($this->local) ? $this->default : $this->local;
        if($value == $input) return false;

        if(!in_array($input, $this->choices)) return false;

        $this->local = $input;
        return true;
    }
}
