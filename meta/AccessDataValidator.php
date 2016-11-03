<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Validate the data for a whole schema
 *
 * Should be aquired through AccessDataTable::getValidator()
 */
class AccessDataValidator extends ValueValidator {

    /** @var AccessTable */
    protected $access;

    /** @var array */
    protected $data;

    /**
     * ValidationResult constructor.
     * @param AccessTable $access
     * @param array $data the data to validate (and save)
     */
    public function __construct(AccessTable $access, $data) {
        parent::__construct();
        $this->access = $access;
        $this->data = $data;
    }

    /**
     * Validate the given data
     *
     * checks for assignments
     * validates
     * returns changed data only
     *
     * @param array $data array('schema' => ( 'fieldlabel' => 'value', ...))
     * @param string $pageid
     * @param string[] $errors validation errors
     * @return AccessDataValidator[]|bool savable data or false on validation error
     */
    static public function validateDataForPage($data, $pageid, &$errors) {
        $tosave = array();
        $valid = true;
        $errors = array();

        $assignments = Assignments::getInstance();
        $tables = $assignments->getPageAssignments($pageid);
        foreach($tables as $table) {
            $access = AccessTable::byTableName($table, $pageid);
            $validation = $access->getValidator($data[$table]);
            if(!$validation->validate()) {
                $valid = false;
                $errors = array_merge($errors, $validation->getErrors());
            } else {
                if($validation->hasChanges()) {
                    $tosave[] = $validation;
                }
            }
        }
        if($valid) return $tosave;
        return false;
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

}
