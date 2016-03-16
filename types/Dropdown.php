<?php
namespace plugin\struct\types;

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
        return $options;
    }

    /**
     * A Dropdown with a single value to pick
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    public function valueEditor($name, $value) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = "<select name=\"$name\" class=\"$class\">";
        foreach($this->getOptions() as $opt) {
            if($opt == $value) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($opt) . "\">" . hsc($opt) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * A dropdown that allows to pick multiple values
     *
     * @param string $name
     * @param \string[] $values
     * @return string
     */
    public function multiValueEditor($name, $values) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = "<select name=\"{$name}[]\" class=\"$class\" multiple=\"multiple\" size=\"5\">";
        foreach($this->getOptions() as $opt) {
            if(in_array($opt, $values)) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($opt) . "\">" . hsc($opt) . '</option>';

        }
        $html .= '</select> ';
        $html .= '<small>' . $this->getLang('multidropdown') . '</small>';
        return $html;
    }

}
