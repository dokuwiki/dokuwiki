<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\ValidationException;

class DateTime extends Date {

    protected $config = array(
        'format' => 'Y/m/d H:i:s',
        'prefilltoday' => false
    );

    /**
     * Return the editor to edit a single value
     *
     * @param string $name the form name where this has to be stored
     * @param string $value the current value
     * @return string html
     */
    public function valueEditor($name, $value) {
        if($this->config['prefilltoday'] && !$value) {
            $value = date('Y-m-d H:i:s');
        }
        return parent::valueEditor($name, $value);
    }

    /**
     * Validate a single value
     *
     * This function needs to throw a validation exception when validation fails.
     * The exception message will be prefixed by the appropriate field on output
     *
     * @param string|array $value
     * @return string
     * @throws ValidationException
     */
    public function validate($value) {
        list($date, $time) = explode(' ', $value, 2);
        $date = trim($date);
        $time = trim($time);

        list($year, $month, $day) = explode('-', $date, 3);
        if(!checkdate($month, $day, $year)) {
            throw new ValidationException('invalid datetime format' . "$year, $month, $day");
        }

        list($h, $m, $s) = explode(':', $time, 3);
        $h = (int) $h;
        $m = (int) $m;
        $s = (int) $s;
        if($h < 0 || $h > 23 || $m < 0 || $m > 59 || $s < 0 || $s > 59) {
            throw new ValidationException('invalid datetime format');
        }

        return sprintf("%d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $h, $m, $s);
    }

}
