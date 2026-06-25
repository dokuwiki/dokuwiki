<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_savedir
 */
class SettingSavedir extends SettingString
{
    /** @inheritdoc */
    public function update($input)
    {
        if ($this->isProtected()) return false;

        $value = $this->local ?? $this->default;
        if ($value == $input) return false;

        if (!init_path($input)) {
            $this->error = true;
            $this->input = $input;
            return false;
        }

        $this->local = $input;
        return true;
    }
}
