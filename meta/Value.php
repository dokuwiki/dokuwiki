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
     * Allows overwriting the current value
     *
     * Cleans the value(s) of empties
     *
     * @param array|int|string $value
     */
    public function setValue($value) {
        if($this->column->isMulti() && !is_array($value)) {
                $value = array($value);
        }

        if(is_array($value)) {
            // remove all blanks
            $value = array_map('trim', $value);
            $value = array_filter($value, array($this, 'filter'));
            $value = array_values($value); // reset keys

            if(!$this->column->isMulti()) {
                $value = (string) array_shift($value);
            }
        } else {
            $value = trim($value);
        }

        $this->value = $value;
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
        return  '' !== ((string) $input);
    }
}
