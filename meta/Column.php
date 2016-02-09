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
    /** @var int the column in the datatable. columns count from 1 */
    protected $colref;
    /** @var bool is this column still enabled? */
    protected $enabled=true;
    /** @var  string backreference to the table this column is part of */
    protected $table;

    /**
     * Column constructor.
     * @param int $sort
     * @param AbstractBaseType $type
     * @param int $colref
     * @param bool $enabled
     * @param string $table
     */
    public function __construct($sort, AbstractBaseType $type, $colref=0, $enabled=true, $table='') {
        $this->sort = (int) $sort;
        $this->type = $type;
        $this->colref = (int) $colref;
        $this->enabled = (bool) $enabled;
        $this->table = $table;
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
        return $this->type->getTid();
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

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * Returns a list of all available types
     *
     * @return array
     */
    static public function allTypes() {
        $types = array();
        $files = glob(DOKU_PLUGIN . 'struct/types/*.php');
        foreach($files as $file) {
            $file = basename($file, '.php');
            if(substr($file, 0, 8) == 'Abstract') continue;
            $types[] = $file;
        }
        sort($types);

        return $types;
    }

}
