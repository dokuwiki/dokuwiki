<?php
namespace dokuwiki\plugin\struct\types;

class Checkbox extends AbstractBaseType {

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
        return $options;
    }

    /**
     * A single checkbox, additional values are ignored
     *
     * @param string $name
     * @param string $rawvalue
     * @param string $htmlID
     *
     * @return string
     */
    public function valueEditor($name, $rawvalue, $htmlID) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $options = $this->getOptions();
        $opt = array_shift($options);

        if($rawvalue == $opt) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        $opt = hsc($opt);
        $id = !empty($htmlID) ? "id=\"$htmlID\"" : '';
        $html = "<label><input type=\"checkbox\" name=\"$name\" class=\"$class\" value=\"$opt\" $id $checked>&nbsp;$opt</label>";
        return $html;
    }

    /**
     * Multiple checkboxes
     *
     * @param string    $name
     * @param \string[] $rawvalues
     * @param string    $htmlID
     *
     * @return string
     */
    public function multiValueEditor($name, $rawvalues, $htmlID) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = '';
        foreach($this->getOptions() as $opt) {
            if(in_array($opt, $rawvalues)) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }

            $id = !empty($htmlID) ? "id=\"$htmlID\"" : '';
            $htmlID = null;

            $opt = hsc($opt);
            $html .= "<label><input type=\"checkbox\" name=\"{$name}[]\" class=\"$class\" value=\"$opt\" $id $checked>&nbsp;$opt</label>";
        }
        return $html;
    }

}
