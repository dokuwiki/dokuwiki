<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_password
 */
class SettingPassword extends SettingString {

    protected $code = 'plain';  // mechanism to be used to obscure passwords

    /** @inheritdoc */
    public function update($input) {
        if($this->isProtected()) return false;
        if(!$input) return false;

        if($this->pattern && !preg_match($this->pattern, $input)) {
            $this->error = true;
            $this->input = $input;
            return false;
        }

        $this->local = conf_encodeString($input, $this->code);
        return true;
    }

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {

        $disable = $this->isProtected() ? 'disabled="disabled"' : '';

        $key = htmlspecialchars($this->key);

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<input id="config___' . $key . '" name="config[' . $key .
            ']" autocomplete="off" type="password" class="edit" value="" ' . $disable . ' />';
        return array($label, $input);
    }
}
