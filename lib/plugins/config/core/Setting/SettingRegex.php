<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_regex
 */
class SettingRegex extends SettingString {

    protected $delimiter = '/';    // regex delimiter to be used in testing input
    protected $pregflags = 'ui';   // regex pattern modifiers to be used in testing input

    /** @inheritdoc */
    public function update($input) {

        // let parent do basic checks, value, not changed, etc.
        $local = $this->local;
        if(!parent::update($input)) return false;
        $this->local = $local;

        // see if the regex compiles and runs (we don't check for effectiveness)
        $regex = $this->delimiter . $input . $this->delimiter . $this->pregflags;
        $lastError = error_get_last();
        @preg_match($regex, 'testdata');
        if(preg_last_error() != PREG_NO_ERROR || error_get_last() != $lastError) {
            $this->input = $input;
            $this->error = true;
            return false;
        }

        $this->local = $input;
        return true;
    }
}
