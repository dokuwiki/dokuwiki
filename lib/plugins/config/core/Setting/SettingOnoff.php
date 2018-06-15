<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_onoff
 */
class SettingOnoff extends SettingNumeric {

    /**
     * We treat the strings 'false' and 'off' as false
     * @inheritdoc
     */
    protected function cleanValue($value) {
        if($value === null) return null;

        if(is_string($value)) {
            if(strtolower($value) === 'false') return 0;
            if(strtolower($value) === 'off') return 0;
            if(trim($value) === '') return 0;
        }

        return (int) (bool) $value;
    }

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        $disable = '';

        if($this->isProtected()) {
            $value = $this->protected;
            $disable = ' disabled="disabled"';
        } else {
            $value = is_null($this->local) ? $this->default : $this->local;
        }

        $key = htmlspecialchars($this->key);
        $checked = ($value) ? ' checked="checked"' : '';

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<div class="input"><input id="config___' . $key . '" name="config[' . $key .
            ']" type="checkbox" class="checkbox" value="1"' . $checked . $disable . '/></div>';
        return array($label, $input);
    }

    /** @inheritdoc */
    public function update($input) {
        if($this->isProtected()) return false;

        $input = ($input) ? 1 : 0;
        $value = is_null($this->local) ? $this->default : $this->local;
        if($value == $input) return false;

        $this->local = $input;
        return true;
    }
}
