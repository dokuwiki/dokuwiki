<?php
namespace plugin\struct\types;

use dokuwiki\Form\Form;

class Text extends AbstractBaseType {

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
    );




    /**
     * Output the stored data
     *
     * @param int|string $value
     * @return string the HTML to represent this data
     */
    public function getDisplayData($value) {
        return hsc($this->config['prefix'] . $value . $this->config['postfix']);
    }

    /**
     * @param string $name
     * @param \string[] $values
     * @return string
     */
    public function multiValueEditor($name, $values) {
        $value = join(', ',$values);
        return $this->valueEditor($name, $value);
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
        $html = "<input name=\"$name\" value=\"$value\" />";
        return "$html";
    }
}
