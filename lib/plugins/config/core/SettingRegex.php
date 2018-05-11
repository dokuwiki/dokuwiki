<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_regex
 */
class SettingRegex extends SettingString {

    protected $_delimiter = '/';    // regex delimiter to be used in testing input
    protected $_pregflags = 'ui';   // regex pattern modifiers to be used in testing input

    /**
     * update changed setting with user provided value $input
     * - if changed value fails error check, save it to $this->_input (to allow echoing later)
     * - if changed value passes error check, set $this->_local to the new value
     *
     * @param  mixed $input the new value
     * @return boolean          true if changed, false otherwise (incl. on error)
     */
    public function update($input) {

        // let parent do basic checks, value, not changed, etc.
        $local = $this->_local;
        if(!parent::update($input)) return false;
        $this->_local = $local;

        // see if the regex compiles and runs (we don't check for effectiveness)
        $regex = $this->_delimiter . $input . $this->_delimiter . $this->_pregflags;
        $lastError = error_get_last();
        @preg_match($regex, 'testdata');
        if(preg_last_error() != PREG_NO_ERROR || error_get_last() != $lastError) {
            $this->_input = $input;
            $this->_error = true;
            return false;
        }

        $this->_local = $input;
        return true;
    }
}
