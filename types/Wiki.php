<?php
namespace dokuwiki\plugin\struct\types;

class Wiki extends AbstractBaseType {

    /**
     * @param int|string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $doc = p_render($mode, p_get_instructions($value), $info);
        $R->doc .= $doc; // FIXME this probably does not work for all renderers
        return true;
    }

    /**
     * Clean line endings
     *
     * @param int|string $value
     * @return int|string
     */
    public function validate($value) {
        $value = parent::validate($value);
        $value = cleanText($value);
        return $value;
    }

    /**
     * Use a text area for input
     *
     * @param string $name
     * @param string $rawvalue
     * @return string
     */
    public function valueEditor($name, $rawvalue) {
        $class = 'struct_'.strtolower($this->getClass());
        $name = hsc($name);
        $rawvalue = formText($rawvalue);

        $html = "<textarea name=\"$name\" class=\"$class\">$rawvalue</textarea>";
        return "$html";
    }

}
