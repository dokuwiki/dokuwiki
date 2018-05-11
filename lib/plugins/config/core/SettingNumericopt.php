<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_numericopt
 */
class SettingNumericopt extends SettingNumeric {
    // just allow an empty config
    protected $_pattern = '/^(|[-]?[0-9]+(?:[-+*][0-9]+)*)$/';

    /**
     * Empty string is valid for numericopt
     *
     * @param mixed $input
     *
     * @return bool
     */
    public function update($input) {
        if($input === '') {
            return true;
        }

        return parent::update($input);
    }
}
