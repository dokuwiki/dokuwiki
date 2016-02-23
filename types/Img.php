<?php
namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Img extends AbstractBaseType {

    protected $config = array(
    );

    /**
     * Output the stored data
     *
     * If outputted in an aggregation we collect the images into a gallery.
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if (empty($value)) {
            return false;
        }

        $returnLink = null;
        if ($mode == 'xhtml') {
            $returnLink = true;
        }
        if (strpos($value, '://') === false) {
            $html = $R->internalmedia($value, null, null, null, null, null, 'direct', $returnLink);
        } else {
            $html = $R->externalmedia($value, null, null, null, null, null, 'direct', $returnLink);
        }

        if ($mode == 'xhtml') {
            $hash = '';
            if (strrpos($R->doc,'</table>') < strrpos($R->doc,'class="table structaggregation')) {
                $hash = substr($R->doc,strpos($R->doc,'hash:',strrpos($R->doc,'class="table structaggregation'))+5,32);
                $hash = "[gal-$hash]";
            }
            $html = str_replace('href', "rel=\"lightbox$hash\" href", $html);
            $R->doc .= $html;
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
