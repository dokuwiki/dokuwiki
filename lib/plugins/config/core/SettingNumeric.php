<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_numeric
 */
class SettingNumeric extends SettingString {
    // This allows for many PHP syntax errors...
    // var $_pattern = '/^[-+\/*0-9 ]*$/';
    // much more restrictive, but should eliminate syntax errors.
    protected $_pattern = '/^[-+]? *[0-9]+ *(?:[-+*] *[0-9]+ *)*$/';
    protected $_min = null;
    protected $_max = null;

    /**
     * update changed setting with user provided value $input
     * - if changed value fails error check, save it to $this->_input (to allow echoing later)
     * - if changed value passes error check, set $this->_local to the new value
     *
     * @param  mixed $input the new value
     * @return boolean          true if changed, false otherwise (also on error)
     */
    public function update($input) {
        $local = $this->_local;
        $valid = parent::update($input);
        if($valid && !(is_null($this->_min) && is_null($this->_max))) {
            $numeric_local = (int) eval('return ' . $this->_local . ';');
            if((!is_null($this->_min) && $numeric_local < $this->_min) ||
                (!is_null($this->_max) && $numeric_local > $this->_max)) {
                $this->_error = true;
                $this->_input = $input;
                $this->_local = $local;
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Generate string to save setting value to file according to $fmt
     *
     * @param string $var name of variable
     * @param string $fmt save format
     * @return string
     */
    public function out($var, $fmt = 'php') {

        if($this->is_protected()) return '';
        if(is_null($this->_local) || ($this->_default == $this->_local)) return '';

        $out = '';

        if($fmt == 'php') {
            $local = $this->_local === '' ? "''" : $this->_local;
            $out .= '$' . $var . "['" . $this->_out_key() . "'] = " . $local . ";\n";
        }

        return $out;
    }
}
