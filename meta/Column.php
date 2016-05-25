<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;

/**
 * Class Column
 *
 * This represents a single column within a schema and contains the configured BaseType as well as the
 * column reference to the data table.
 *
 * It basically combines the information how a column's content behaves (as defines in the BaseType and its
 * configuration) with where to find that content and adds some basic meta data (like sort or enabled)
 *
 * @package dokuwiki\plugin\struct\meta
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
     * @return string
     */
    public function getLabel() {
        return $this->type->getLabel();
    }

    /**
     * @return string the label prepended with the table name
     */
    public function getFullQualifiedLabel() {
        if(!$this->table) throw new StructException('No table set for this column');
        return $this->table .'.'. $this->getLabel();
    }

    /**
     * @return string
     */
    public function getTranslatedLabel() {
        return $this->type->getTranslatedLabel();
    }

    /**
     * @return string
     */
    public function getTranslatedHint() {
        return $this->type->getTranslatedHint();
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
     * Returns the full column name. When table is set, prefixed by the table name
     *
     * @param bool $forceSingleColumn Throw an exception if $this is a multi column
     * @return string
     */
    public function getColName($forceSingleColumn = true) {
        if($forceSingleColumn && $this->isMulti()) throw new StructException('Calling getColName on a multi value column makes no sense.');

        $col = 'col'.$this->colref;
        if($this->table) $col = 'data_'.$this->table.'.'.$col;
        return $col;
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
     * @return bool
     */
    public function isMulti() {
        return $this->type->isMulti();
    }

    /**
     * @return bool
     */
    public function isVisibleInEditor() {
        return $this->getType()->isVisibleInEditor();
    }

    /**
     * @return bool
     */
    public function isVisibleInPage() {
        return $this->getType()->isVisibleInPage();
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
