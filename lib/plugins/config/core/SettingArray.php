<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_array
 */
class SettingArray extends Setting {

    /**
     * Create an array from a string
     *
     * @param string $string
     * @return array
     */
    protected function _from_string($string) {
        $array = explode(',', $string);
        $array = array_map('trim', $array);
        $array = array_filter($array);
        $array = array_unique($array);
        return $array;
    }

    /**
     * Create a string from an array
     *
     * @param array $array
     * @return string
     */
    protected function _from_array($array) {
        return join(', ', (array) $array);
    }

    /**
     * update setting with user provided value $input
     * if value fails error check, save it
     *
     * @param string $input
     * @return bool true if changed, false otherwise (incl. on error)
     */
    public function update($input) {
        if(is_null($input)) return false;
        if($this->is_protected()) return false;

        $input = $this->_from_string($input);

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;

        foreach($input as $item) {
            if($this->_pattern && !preg_match($this->_pattern, $item)) {
                $this->_error = true;
                $this->_input = $input;
                return false;
            }
        }

        $this->_local = $input;
        return true;
    }

    /**
     * Escaping
     *
     * @param string $string
     * @return string
     */
    protected function _escape($string) {
        $tr = array("\\" => '\\\\', "'" => '\\\'');
        return "'" . strtr(cleanText($string), $tr) . "'";
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
            $vals = array_map(array($this, '_escape'), $this->_local);
            $out = '$' . $var . "['" . $this->_out_key() . "'] = array(" . join(', ', $vals) . ");\n";
        }

        return $out;
    }

    /**
     * Build html for label and input of setting
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @param bool $echo true: show inputted value, when error occurred, otherwise the stored setting
     * @return string[] with content array(string $label_html, string $input_html)
     */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        $disable = '';

        if($this->is_protected()) {
            $value = $this->_protected;
            $disable = 'disabled="disabled"';
        } else {
            if($echo && $this->_error) {
                $value = $this->_input;
            } else {
                $value = is_null($this->_local) ? $this->_default : $this->_local;
            }
        }

        $key = htmlspecialchars($this->_key);
        $value = htmlspecialchars($this->_from_array($value));

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<input id="config___' . $key . '" name="config[' . $key .
            ']" type="text" class="edit" value="' . $value . '" ' . $disable . '/>';
        return array($label, $input);
    }
}
