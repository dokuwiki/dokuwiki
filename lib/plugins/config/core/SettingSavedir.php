<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_savedir
 */
class SettingSavedir extends SettingString {

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

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;

        if(!init_path($input)) {
            $this->_error = true;
            $this->_input = $input;
            return false;
        }

        $this->_local = $input;
        return true;
    }
}
