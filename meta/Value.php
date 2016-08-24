<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class Value
 *
 * Holds the value for a single "cell". That value may be an array for multi value columns
 *
 * @package dokuwiki\plugin\struct\meta
 */
class Value {

    /** @var Column */
    protected $column;

    /** @var  array|int|string */
    protected $value;

    /** @var  array|int|string */
    protected $rawvalue = null;

    /** @var array|int|string */
    protected $display = null;

    /**
     * Value constructor.
     *
     * @param Column $column
     * @param array|int|string $value
     */
    public function __construct(Column $column, $value) {
        $this->column = $column;
        $this->setValue($value);
    }

    /**
     * @return Column
     */
    public function getColumn() {
        return $this->column;
    }

    /**
     * @return array|int|string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Access the raw value
     *
     * @return array|string (array on multi)
     */
    public function getRawValue() {
        return $this->rawvalue;
    }

    /**
     * Access the display value
     *
     * @return array|string (array on multi)
     */
    public function getDisplayValue() {
        return $this->display;
    }

    /**
     * Allows overwriting the current value
     *
     * Cleans the value(s) of empties
     *
     * @param array|int|string $value
     */
    public function setValue($value) {
        // treat all givens the same
        if(!is_array($value)) {
            $value = array($value);
        }

        // reset/init
        $this->value = array();
        $this->rawvalue = array();
        $this->display = array();

        // remove all blanks
        foreach($value as $val) {
            $val = trim($val);
            $raw = $this->column->getType()->rawValue($val);
            if('' === (string) $raw) continue;
            $this->value[] = $val;
            $this->rawvalue[] = $raw;
            $this->display[] = $this->column->getType()->displayValue($val);
        }

        // make single value again
        if(!$this->column->isMulti()) {
            $this->value = (string) array_shift($this->value);
            $this->rawvalue = (string) array_shift($this->rawvalue);
            $this->display = (string) array_shift($this->display);
        }
    }

    /**
     * Is this empty?
     *
     * @return bool
     */
    public function isEmpty() {
        return ($this->rawvalue === '' || $this->rawvalue === array());
    }

    /**
     * Render the value using the given renderer and mode
     *
     * automativally picks the right mechanism depending on multi or single value
     *
     * values are only rendered when there is a value
     *
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function render(\Doku_Renderer $R, $mode) {
        if($this->column->isMulti()) {
            if(count($this->value)) {
                return $this->column->getType()->renderMultiValue($this->value, $R, $mode);
            }
        } else {
            if($this->value !== '') {
                return $this->column->getType()->renderValue($this->value, $R, $mode);
            }
        }
        return true;
    }

    /**
     * Return the value editor for this value field
     *
     * @param string $name The field name to use in the editor
     * @return string The HTML for the editor
     */
    public function getValueEditor($name) {
        if($this->column->isMulti()) {
            return $this->column->getType()->multiValueEditor($name, $this->value);
        } else {
            return $this->column->getType()->valueEditor($name, $this->value);
        }
    }

    /**
     * Filter callback to strip empty values
     *
     * @param string $input
     * @return bool
     */
    public function filter($input) {
        return '' !== ((string) $input);
    }
}
