<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\ValidationException;

class Color extends AbstractBaseType {

    protected $config = array(
        'default' => '#ffffff'
    );

    /**
     * @inheritDoc
     */
    public function validate($rawvalue) {
        $rawvalue = trim(strtolower($rawvalue));
        if(!preg_match('/^#[a-f0-9]{6}$/', $rawvalue)) {
            throw new ValidationException('bad color specification');
        }

        // ignore if default
        if($rawvalue == strtolower($this->config['default'])) {
            $rawvalue = '';
        }

        return $rawvalue;
    }

    /**
     * @inheritDoc
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if($mode == 'xhtml') {
            $R->doc .= '<div title="' . hsc($value) . '" style="background-color:' . hsc($value) . ';" class="struct_color"></div>';
        } else {
            $R->cdata($value);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function renderMultiValue($values, \Doku_Renderer $R, $mode) {
        if($mode == 'xhtml') {
            foreach($values as $value) {
                $this->renderValue($value, $R, $mode);
            }
        } else {
            $R->cdata(join(', ', $values));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function valueEditor($name, $rawvalue) {
        $class = 'struct_color';
        if($rawvalue == '') $rawvalue = $this->config['default'];
        $name = hsc($name);
        $rawvalue = hsc($rawvalue);
        $html = "<input name=\"$name\" value=\"$rawvalue\" class=\"$class\" type=\"color\" />";
        return "$html";
    }

}
