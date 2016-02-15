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
        $value = join(', ', $values);
        return $this->valueEditor($name, $value);
    }

}
