<?php

namespace dokuwiki\plugin\config\core\Setting;

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
    protected function fromString($string) {
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
    protected function fromArray($array) {
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
        if($this->isProtected()) return false;

        $input = $this->fromString($input);

        $value = is_null($this->local) ? $this->default : $this->local;
        if($value == $input) return false;

        foreach($input as $item) {
            if($this->pattern && !preg_match($this->pattern, $item)) {
                $this->error = true;
                $this->input = $input;
                return false;
            }
        }

        $this->local = $input;
        return true;
    }

    /**
     * Escaping
     *
     * @param string $string
     * @return string
     */
    protected function escape($string) {
        $tr = array("\\" => '\\\\', "'" => '\\\'');
        return "'" . strtr(cleanText($string), $tr) . "'";
    }

    /** @inheritdoc */
    public function out($var, $fmt = 'php') {
        if($fmt != 'php') return '';

        $vals = array_map(array($this, 'escape'), $this->local);
        $out = '$' . $var . "['" . $this->getArrayKey() . "'] = array(" . join(', ', $vals) . ");\n";
        return $out;
    }

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        $disable = '';

        if($this->isProtected()) {
            $value = $this->protected;
            $disable = 'disabled="disabled"';
        } else {
            if($echo && $this->error) {
                $value = $this->input;
            } else {
                $value = is_null($this->local) ? $this->default : $this->local;
            }
        }

        $key = htmlspecialchars($this->key);
        $value = htmlspecialchars($this->fromArray($value));

        $label = '<label for="config___' . $key . '">' . $this->prompt($plugin) . '</label>';
        $input = '<input id="config___' . $key . '" name="config[' . $key .
            ']" type="text" class="edit" value="' . $value . '" ' . $disable . '/>';
        return array($label, $input);
    }
}
