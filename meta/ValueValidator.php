<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;

/**
 * Validator to validate a single value
 */
class ValueValidator {

    /** @var  \helper_plugin_struct_db */
    protected $hlp;

    /** @var  array list of validation errors */
    protected $errors;

    /**
     * ValueValidator constructor.
     */
    public function __construct() {
        $this->hlp = plugin_load('helper', 'struct_db');
        $this->errors = array();
    }

    /**
     * Validate a single value
     *
     * @param Column $col the column of that value
     * @param mixed &$rawvalue the value, will be fixed according to the type
     * @return bool
     */
    public function validateValue(Column $col, &$rawvalue) {
        // fix multi value types
        $type = $col->getType();
        $trans = $type->getTranslatedLabel();
        if($type->isMulti() && !is_array($rawvalue)) {
            $rawvalue = $type->splitValues($rawvalue);
        }
        // strip empty fields from multi vals
        if(is_array($rawvalue)) {
            $rawvalue = array_filter($rawvalue, array($this, 'filter'));
            $rawvalue = array_values($rawvalue); // reset the array keys
        }

        // validate data
        return $this->validateField($type, $trans, $rawvalue);
    }

    /**
     * The errors that occured during validation
     *
     * @return string[] already translated error messages
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Validate the given data for a single field
     *
     * Catches the Validation exceptions and transforms them into proper error messages.
     *
     * Blank values are not validated and always pass
     *
     * @param AbstractBaseType $type
     * @param string $label
     * @param array|string|int &$data may be modified by the validation function
     * @return bool true if the data validates, otherwise false
     */
    protected function validateField(AbstractBaseType $type, $label, &$data) {
        $prefix = sprintf($this->hlp->getLang('validation_prefix'), $label);

        $ok = true;
        if(is_array($data)) {
            foreach($data as &$value) {
                if(!blank($value)) {
                    try {
                        $value = $type->validate($value);
                    } catch(ValidationException $e) {
                        $this->errors[] = $prefix . $e->getMessage();
                        $ok = false;
                    }
                }
            }
            return $ok;
        }

        if(!blank($data)) {
            try {
                $data = $type->validate($data);
            } catch(ValidationException $e) {
                $this->errors[] = $prefix . $e->getMessage();
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Simple filter to remove blank values
     *
     * @param string $val
     * @return bool
     */
    public function filter($val) {
        return !blank($val);
    }
}
