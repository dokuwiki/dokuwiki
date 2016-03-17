<?php
namespace plugin\struct\types;

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
     * @param string $value
     * @return string
     */
    public function valueEditor($name, $value) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $options = $this->getOptions();
        $opt = array_shift($options);

        if($value == $opt) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        $opt = hsc($opt);
        $html = "<label><input type=\"checkbox\" name=\"$name\" class=\"$class\" value=\"$opt\" $checked>&nbsp;$opt</label>";
        return $html;
    }

    /**
     * Multiple checkboxes
     *
     * @param string $name
     * @param \string[] $values
     * @return string
     */
    public function multiValueEditor($name, $values) {
        $class = 'struct_' . strtolower($this->getClass());

        $name = hsc($name);
        $html = '';
        foreach($this->getOptions() as $opt) {
            if(in_array($opt, $values)) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }

            $opt = hsc($opt);
            $html .= "<label><input type=\"checkbox\" name=\"{$name}[]\" class=\"$class\" value=\"$opt\" $checked>&nbsp;$opt</label>";
        }
        return $html;
    }

}
