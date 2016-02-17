<?php

namespace plugin\struct\meta;

/**
 * Class Value
 *
 * Holds the value for a single "cell". That value may be an array for multi value columns
 *
 * @package plugin\struct\meta
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
        if($column->isMulti() && !is_array($value)) {
            $value = array($value);
        }
        $this->value = $value;
        $this->column = $column;
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
     * Render the value using the given renderer and mode
     *
     * automativally picks the right mechanism depending on multi or single value
     *
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function render(\Doku_Renderer $R, $mode) {
        if($this->column->isMulti()) {
            return $this->column->getType()->renderMultiValue($this->value, $R, $mode);
        } else {
            return $this->column->getType()->renderValue($this->value, $R, $mode);
        }
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
}
