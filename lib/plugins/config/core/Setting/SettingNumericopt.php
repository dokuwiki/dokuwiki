<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_numericopt
 */
class SettingNumericopt extends SettingNumeric {
    // just allow an empty config
    protected $pattern = '/^(|[-]?[0-9]+(?:[-+*][0-9]+)*)$/';

    /**
     * @inheritdoc
     * Empty string is valid for numericopt
     */
    public function update($input) {
        if($input === '') {
            if($input == $this->local) return false;
            $this->local = $input;
            return true;
        }

        return parent::update($input);
    }
}
