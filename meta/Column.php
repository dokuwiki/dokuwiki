<?php

namespace plugin\struct\meta;

use plugin\struct\types\AbstractBaseType;

/**
 * Class Column
 *
 * This represents a single column within a schema and contains the configured BaseType as well as the
 * column reference to the data table.
 *
 * It basically combines the information how a column's content behaves (as defines in the BaseType and its
 * configuration) with where to find that content and adds some basic meta data (like sort or enabled)
 *
 * @package plugin\struct\meta
 */
class Column {

    /** @var int fields are sorted by this value */
    protected $sort;
    /** @var AbstractBaseType the type of this column */
    protected $type;
    /** @var int the ID of the currently used type */
    protected $tid;
    /** @var int the column in the datatable. columns count from 1 */
    protected $colref;
    /** @var bool is this column still enabled? */
    protected $enabled=true;

    /**
     * Column constructor.
     * @param int $sort
     * @param AbstractBaseType $type
     * @param int $tid
     * @param int $colref
     * @param bool $enabled
     */
    public function __construct($sort, AbstractBaseType $type, $tid = 0, $colref=0, $enabled=true) {
        $this->sort = (int) $sort;
        $this->type = $type;
        $this->tid = (int) $tid;
        $this->colref = (int) $colref;
        $this->enabled = (bool) $enabled;
    }

    /**
     * @return int
     */
    public function getSort() {
        return $this->sort;
    }

    /**
     * @return int
     */
    public function getTid() {
        return $this->tid;
    }

    /**
     * @return AbstractBaseType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getColref() {
        return $this->colref;
    }

    /**
     * @return boolean
     */
    public function isEnabled() {
        return $this->enabled;
    }



}
