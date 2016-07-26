<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\ValidationException;

class Date extends AbstractBaseType {

    protected $config = array(
        'format' => 'Y/m/d',
        'prefilltoday' => false
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
        $date = date_create($value);
        if($date !== false) {
            $out = date_format($date, $this->config['format']);
        } else {
            $out = '';
        }

        $R->cdata($out);
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

        if($this->config['prefilltoday'] && !$value) {
            $value = date('Y-m-d');
        }

        $html = "<input class=\"struct_date\" name=\"$name\" value=\"$value\" />";
        return "$html";
    }

    /**
     * Validate a single value
     *
     * This function needs to throw a validation exception when validation fails.
     * The exception message will be prefixed by the appropriate field on output
     *
     * @param string|int $value
     * @return int|string
     * @throws ValidationException
     */
    public function validate($value) {
        $value = parent::validate($value);
        list($value) = explode(' ', $value, 2); // strip off time if there is any

        list($year, $month, $day) = explode('-',$value, 3);
        if (!checkdate($month, $day, $year)) {
            throw new ValidationException('invalid date format');
        }
        return $value;
    }

}
