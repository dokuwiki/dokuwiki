<?php
namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Integer extends Text {

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
        'format' => '%d'
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
        $R->cdata($this->config['prefix'] . sprintf($this->config['format'], $value) . $this->config['postfix']);
        return true;
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

    /**
     * @param int|string $value
     * @throws ValidationException
     */
    public function validate($value) {
        $value = trim($value);

        if((string) $value != (string) intval($value)) {
            throw new ValidationException('Integer needed');
        }
    }

}
