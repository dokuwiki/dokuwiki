<?php
namespace dokuwiki\plugin\struct\types;

class Dropdown extends AbstractBaseType {

    protected $config = array(
        'values' => 'one, two, three',
    );

    /**
     * Creates the options array
     *
     * @return array
     */
    protected function getOptions() {
        $options = explode(',', $this->config['values']);
        $options = array_map('trim', $options);
        $options = array_filter($options);
        array_unshift($options, '');
        $options = array_combine($options, $options);
        return $options;
    }

    /**
     * A Dropdown with a single value to pick
     *
     * @param string $name
     * @param string $rawvalue
     * @return string
     */
    public function valueEditor($name, $rawvalue) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = "<select name=\"$name\" class=\"$class\">";
        foreach($this->getOptions() as $opt => $val) {
            if($opt == $rawvalue) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($opt) . "\">" . hsc($val) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * A dropdown that allows to pick multiple values
     *
     * @param string $name
     * @param \string[] $rawvalues
     * @return string
     */
    public function multiValueEditor($name, $rawvalues) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = "<select name=\"{$name}[]\" class=\"$class\" multiple=\"multiple\" size=\"5\">";
        foreach($this->getOptions() as $raw => $opt) {
            if(in_array($opt, $rawvalues)) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($raw) . "\">" . hsc($opt) . '</option>';

        }
        $html .= '</select> ';
        $html .= '<small>' . $this->getLang('multidropdown') . '</small>';
        return $html;
    }
}
