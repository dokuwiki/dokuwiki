<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_im_convert
 */
class SettingImConvert extends SettingString {

    /** @inheritdoc */
    public function update($input) {
        if($this->isProtected()) return false;

        $input = trim($input);

        $value = is_null($this->local) ? $this->default : $this->local;
        if($value == $input) return false;

        if($input && !file_exists($input)) {
            $this->error = true;
            $this->input = $input;
            return false;
        }

        $this->local = $input;
        return true;
    }
}
