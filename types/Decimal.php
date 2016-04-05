<?php
namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

/**
 * Class Decimal
 *
 * A field accepting decimal numbers
 *
 * @package plugin\struct\types
 */
class Decimal extends AbstractMultiBaseType {

    protected $config = array(
        'format' => '%f',
        'min' => '',
        'max' => '',
        'decpoint' => '.'
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
        $value = sprintf($this->config['format'], $value);
        $value = str_replace('.', $this->config['decpoint'], $value);
        $R->cdata($value);
        return true;
    }

    /**
     * @param int|string $value
     * @return int|string
     * @throws ValidationException
     */
    public function validate($value) {
        $value = parent::validate($value);
        $value = str_replace(',', '.', $value); // we accept both

        if((string) $value != (string) floatval($value)) {
            throw new ValidationException('Decimal needed');
        }

        if($this->config['min'] !== '' && floatval($value) <= floatval($this->config['min'])) {
            throw new ValidationException('Decimal min', floatval($this->config['min']));
        }

        if($this->config['max'] !== '' && floatval($value) >= floatval($this->config['max'])) {
            throw new ValidationException('Decimal max', floatval($this->config['max']));
        }

        return $value;
    }

}
