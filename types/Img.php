<?php
namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Img extends AbstractBaseType {

    protected $config = array(
    );

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if (!empty($value)) {
            if (strpos($value, '://') === false) {
                $R->internalmedia($value);
            } else {
                $R->externalmedia($value);
            }
        }
        return true;
    }

    /**
     * Return the editor to edit a single value
     *
     * @param string $name the form name where this has to be stored
     * @param string $value the current value
     * @return string html
     */
    public function valueEditor($name, $value) {
        $name = hsc($name);
        $value = hsc($value);

        $id = preg_replace('(\[|\])','',substr($name,strpos($name,'[')+1));

        $html = "<input id=\"$id\" class=\"struct_img\"  name=\"$name\" value=\"$value\" />";
        $html .= "<button type=\"button\" class=\"struct_img\" id=\"$id"."Button\">";
        $html .= "<img src=\"" . DOKU_BASE . "lib/images/toolbar/image.png\" height=\"16\" width=\"16\">";
        $html .= "</button>";
        return $html;
    }
}
