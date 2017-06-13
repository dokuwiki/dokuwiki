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
    public function valueEditor($name, $rawvalue, $htmlID) {
        $params = array(
            'name' => $name,
            'class' => 'struct_'.strtolower($this->getClass()),
            'id' => $htmlID
        );
        $attributes = buildAttributes($params, true);
        $html = "<select $attributes>";
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
     * @param string    $name
     * @param \string[] $rawvalues
     * @param string    $htmlID
     *
     * @return string
     */
    public function multiValueEditor($name, $rawvalues, $htmlID) {
        $params = array(
            'name' => $name . '[]',
            'class' => 'struct_'.strtolower($this->getClass()),
            'multiple' => 'multiple',
            'size' => '5',
            'id' => $htmlID
        );
        $attributes = buildAttributes($params, true);
        $html = "<select $attributes>";
        foreach($this->getOptions() as $raw => $opt) {
            if(in_array($raw, $rawvalues)) {
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
