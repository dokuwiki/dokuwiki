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
     * @param \Doku_Renderer $R
     * @param string $mode
     */
    public function render(\Doku_Renderer $R, $mode) {
        if($this->column->isMulti()) {
            $this->column->getType()->renderMultiValue($this->value, $R, $mode);
        } else {
            $this->column->getType()->renderValue($this->value, $R, $mode);
        }
    }
}
