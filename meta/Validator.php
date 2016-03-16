<?php

namespace plugin\struct\meta;

use plugin\struct\types\AbstractBaseType;

/**
 * Validate input data against schemas and their field validators
 *
 * @package plugin\struct\meta
 */
class Validator {

    /** @var array */
    protected $data = array();

    /** @var array of the given data only these schemas need to be saved */
    protected $tosave = array();

    /** @var array a list of validation errors */
    protected $errors = array();

    /** @var  \helper_plugin_struct_db */
    protected $hlp;

    /**
     * Validator constructor.
     */
    public function __construct() {
        $this->hlp = plugin_load('helper', 'struct_db');
    }

    /**
     * Does the actual validation
     *
     * the given data is cleaned up and returned (ommiting unavailable schemas and
     * making sure multi value fields are arrays)
     *
     * internal result vars are set here as well.
     *
     * @param array $data
     * @param string $id
     * @return bool did the data validate without errors?
     */
    public function validate($data, $id) {
        $this->data = array();
        $this->errors = array();
        $result = true;

        $assignments = new Assignments();
        $tables = $assignments->getPageAssignments($id);

        foreach($tables as $table) {
            $schemaData = new SchemaData($table, $id, time());
            if(!$schemaData->getId()) {
                // this schema is not available for some reason. skip it
                continue;
            }

            $newData = $data[$table];
            foreach($schemaData->getColumns() as $col) {
                // fix multi value types
                $type = $col->getType();
                $label = $type->getLabel();
                $trans = $type->getTranslatedLabel();
                if($type->isMulti() && !is_array($newData[$label])) {
                    $newData[$label] = $type->splitValues($newData[$label]);
                }
                // strip empty fields from multi vals
                if(is_array($newData[$label])) {
                    $newData[$label] = array_filter($newData[$label], array($this, 'filter'));
                    $newData[$label] = array_values($newData[$label]); // reset the array keys
                }

                // validate data
                $result = $result && $this->validateField($type, $trans, $newData[$label]);
            }

            // has the data changed? mark it for saving.
            $olddata = $schemaData->getDataArray();
            if($olddata != $newData) {
                $this->tosave[] = $table;
            }

            // write back cleaned up data
            $this->data[$table] = $newData;
        }

        return $result;
    }

    /**
     * @return array the input data cleaned uo
     */
    public function getCleanedData() {
        return $this->data;
    }

    /**
     * @return array a list of error messages if validation failed
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * @return array the list of schemas that changed and actually have to be saved
     */
    public function getChangedSchemas() {
        return $this->tosave;
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
