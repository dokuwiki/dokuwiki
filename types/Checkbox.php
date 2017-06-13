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
        $options = $this->getOptions();
        $opt = array_shift($options);

        if($rawvalue == $opt) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $opt = hsc($opt);
        $params = array(
            'name' => $name,
            'value' => $opt,
            'class' => 'struct_' . strtolower($this->getClass()),
            'type' => 'checkbox',
            'id' => $htmlID,
            'checked' => $checked,
        );
        $attributes = buildAttributes($params, true);
        return "<label><input $attributes>&nbsp;$opt</label>";
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

        $html = '';
        foreach($this->getOptions() as $opt) {
            if(in_array($opt, $rawvalues)) {
                $checked = 'checked';
            } else {
                $checked = '';
            }

            $params = array(
                'name' => $name . '[]',
                'value' => $opt,
                'class' => $class,
                'type' => 'checkbox',
                'id' => $htmlID,
                'checked' => $checked,
            );
            $attributes = buildAttributes($params, true);
            $htmlID = '';

            $opt = hsc($opt);
            $html .= "<label><input $attributes>&nbsp;$opt</label>";
        }
        return $html;
    }

}
