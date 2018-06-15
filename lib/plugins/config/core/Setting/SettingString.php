<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_string
 */
class SettingString extends Setting {
    /** @inheritdoc */
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
        $value = htmlspecialchars($value);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<input id="config___' . $key . '" name="config[' . $key .
            ']" type="text" class="edit" value="' . $value . '" ' . $disable . '/>';
        return array($label, $input);
    }
}
