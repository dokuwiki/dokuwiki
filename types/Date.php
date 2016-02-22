<?php
namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Date extends AbstractBaseType {

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
        'format' => 'Y/m/d'
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
        $R->cdata($this->config['prefix'] . date_format($date, $this->config['format']) . $this->config['postfix']);
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
        $html = "<input class=\"struct_date\" name=\"$name\" value=\"$value\" />";
        return "$html";
    }

    public function validate($value) {
        list($year, $month, $day) = explode('-',$value);
        if (!checkdate($month, $day, $year)) {
            throw new ValidationException('invalid date format');
        }
    }

}
