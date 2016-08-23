<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;

// FIXME rename
class ValidationResult {

    /** @var  \helper_plugin_struct_db */
    protected $hlp;

    /** @var AccessTable */
    protected $access;

    /** @var array */
    protected $data;

    /** @var  array list of validation errors */
    protected $errors;

    /**
     * ValidationResult constructor.
     * @param AccessTable $access
     * @param array $data the data to validate (and save)
     */
    public function __construct(AccessTable $access, $data) {
        $this->hlp = plugin_load('helper', 'struct_db');
        $this->access = $access;
        $this->data = $data;
        $this->errors = array();
    }

    /**
     * Validate the data. This will clean the data according to type!
     *
     * @return bool
     */
    public function validate() {
        $result = true;
        foreach($this->access->getSchema()->getColumns() as $col) {
            $label = $col->getType()->getLabel();
            $result = $result && $this->validateValue($col, $this->data[$label]);
        }
        return $result;
    }

    /**
     * Check if the data changed (selects current data)
     *
     * @return bool
     */
    public function hasChanges() {
        $olddata = $this->access->getDataArray();
        return ($olddata != $this->data);
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
     * @return AccessTable
     */
    public function getAccessTable() {
        return $this->access;
    }

    /**
     * Access the data after it has been cleand in the validation process
     *
     * @return array
     */
    public function getCleanData() {
        return $this->data;
    }

    /**
     * Saves the data
     *
     * This saves no matter what. You have to chcek validation results and changes on your own!
     *
     * @param int $ts the timestamp to use when saving the data
     * @return bool
     */
    public function saveData($ts = 0) {
        $this->access->setTimestamp($ts);
        return $this->access->saveData($this->data);
    }

    /**
     * Validate a single value
     *
     * @param Column $col the column of that value
     * @param mixed &$value the value, will be fixed according to the type
     * @return bool
     */
    protected function validateValue(Column $col, &$value) {
        // fix multi value types
        $type = $col->getType();
        $trans = $type->getTranslatedLabel();
        if($type->isMulti() && !is_array($value)) {
            $value = $type->splitValues($value);
        }
        // strip empty fields from multi vals
        if(is_array($value)) {
            $value = array_filter($value, array($this, 'filter'));
            $value = array_values($value); // reset the array keys
        }

        // validate data
        return $this->validateField($type, $trans, $value);
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
