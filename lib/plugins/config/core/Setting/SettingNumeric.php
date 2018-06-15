<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_numeric
 */
class SettingNumeric extends SettingString {
    // This allows for many PHP syntax errors...
    // var $_pattern = '/^[-+\/*0-9 ]*$/';
    // much more restrictive, but should eliminate syntax errors.
    protected $pattern = '/^[-+]? *[0-9]+ *(?:[-+*] *[0-9]+ *)*$/';
    protected $min = null;
    protected $max = null;

    /** @inheritdoc */
    public function update($input) {
        $local = $this->local;
        $valid = parent::update($input);
        if($valid && !(is_null($this->min) && is_null($this->max))) {
            $numeric_local = (int) eval('return ' . $this->local . ';');
            if((!is_null($this->min) && $numeric_local < $this->min) ||
                (!is_null($this->max) && $numeric_local > $this->max)) {
                $this->error = true;
                $this->input = $input;
                $this->local = $local;
                $valid = false;
            }
        }
        return $valid;
    }

    /** @inheritdoc */
    public function out($var, $fmt = 'php') {
        if($fmt != 'php') return '';

        $local = $this->local === '' ? "''" : $this->local;
        $out = '$' . $var . "['" . $this->getArrayKey() . "'] = " . $local . ";\n";

        return $out;
    }
}
