<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_multicheckbox
 */
class SettingMulticheckbox extends SettingString {

    protected $_choices = array();
    protected $_combine = array();
    protected $_other = 'always';

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

        // split any combined values + convert from array to comma separated string
        $input = ($input) ? $input : array();
        $input = $this->_array2str($input);

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;

        if($this->_pattern && !preg_match($this->_pattern, $input)) {
            $this->_error = true;
            $this->_input = $input;
            return false;
        }

        $this->_local = $input;
        return true;
    }

    /**
     * Build html for label and input of setting
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @param bool $echo true: show input value, when error occurred, otherwise the stored setting
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

        // convert from comma separated list into array + combine complimentary actions
        $value = $this->_str2array($value);
        $default = $this->_str2array($this->_default);

        $input = '';
        foreach($this->_choices as $choice) {
            $idx = array_search($choice, $value);
            $idx_default = array_search($choice, $default);

            $checked = ($idx !== false) ? 'checked="checked"' : '';

            // @todo ideally this would be handled using a second class of "default"
            $class = (($idx !== false) == (false !== $idx_default)) ? " selectiondefault" : "";

            $prompt = ($plugin->getLang($this->_key . '_' . $choice) ?
                $plugin->getLang($this->_key . '_' . $choice) : htmlspecialchars($choice));

            $input .= '<div class="selection' . $class . '">' . "\n";
            $input .= '<label for="config___' . $key . '_' . $choice . '">' . $prompt . "</label>\n";
            $input .= '<input id="config___' . $key . '_' . $choice . '" name="config[' . $key .
                '][]" type="checkbox" class="checkbox" value="' . $choice . '" ' . $disable . ' ' . $checked . "/>\n";
            $input .= "</div>\n";

            // remove this action from the disabledactions array
            if($idx !== false) unset($value[$idx]);
            if($idx_default !== false) unset($default[$idx_default]);
        }

        // handle any remaining values
        if($this->_other != 'never') {
            $other = join(',', $value);
            // test equivalent to ($this->_other == 'always' || ($other && $this->_other == 'exists')
            // use != 'exists' rather than == 'always' to ensure invalid values default to 'always'
            if($this->_other != 'exists' || $other) {

                $class = (
                    (count($default) == count($value)) &&
                    (count($value) == count(array_intersect($value, $default)))
                ) ?
                    " selectiondefault" : "";

                $input .= '<div class="other' . $class . '">' . "\n";
                $input .= '<label for="config___' . $key . '_other">' . $plugin->getLang($key . '_other') . "</label>\n";
                $input .= '<input id="config___' . $key . '_other" name="config[' . $key .
                    '][other]" type="text" class="edit" value="' . htmlspecialchars($other) . '" ' . $disable . " />\n";
                $input .= "</div>\n";
            }
        }
        $label = '<label>' . $this->prompt($plugin) . '</label>';
        return array($label, $input);
    }

    /**
     * convert comma separated list to an array and combine any complimentary values
     *
     * @param string $str
     * @return array
     */
    protected function _str2array($str) {
        $array = explode(',', $str);

        if(!empty($this->_combine)) {
            foreach($this->_combine as $key => $combinators) {
                $idx = array();
                foreach($combinators as $val) {
                    if(($idx[] = array_search($val, $array)) === false) break;
                }

                if(count($idx) && $idx[count($idx) - 1] !== false) {
                    foreach($idx as $i) unset($array[$i]);
                    $array[] = $key;
                }
            }
        }

        return $array;
    }

    /**
     * convert array of values + other back to a comma separated list, incl. splitting any combined values
     *
     * @param array $input
     * @return string
     */
    protected function _array2str($input) {

        // handle other
        $other = trim($input['other']);
        $other = !empty($other) ? explode(',', str_replace(' ', '', $input['other'])) : array();
        unset($input['other']);

        $array = array_unique(array_merge($input, $other));

        // deconstruct any combinations
        if(!empty($this->_combine)) {
            foreach($this->_combine as $key => $combinators) {

                $idx = array_search($key, $array);
                if($idx !== false) {
                    unset($array[$idx]);
                    $array = array_merge($array, $combinators);
                }
            }
        }

        return join(',', array_unique($array));
    }
}
