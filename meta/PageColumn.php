<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;

/**
 * Class PageColumn
 *
 * Just like a column, but does not reference one of the col* data columns but the pid column.
 *
 * @package dokuwiki\plugin\struct\meta
 */
class PageColumn extends Column {

    /**
     * PageColumn constructor.
     *
     * @param int $sort
     * @param AbstractBaseType $type This should be Page or Title
     * @param string $table
     */
    public function __construct($sort, AbstractBaseType $type, $table='') {
        if($type->isMulti()) throw new StructException('PageColumns can not be multi value types!');
        parent::__construct($sort, $type, 0, true, $table);
    }

    public function getColref() {
        throw new StructException('Accessing the colref of a PageColumn makes no sense');
    }

    /**
     * @param bool $forceSingleColumn ignored
     * @return string
     */
    public function getColName($forceSingleColumn = true) {
        $col = 'pid';
        if($this->table) $col = 'data_'.$this->table.'.'.$col;
        return $col;
    }

    /**
     * @return string always '%pageid%'
     */
    public function getLabel() {
        return '%pageid%';
    }

    /**
     * @return string always '%pageid%'
     */
    public function getFullQualifiedLabel() {
        // There is only one pageid for each row because we JOIN on it
        // so we do not prefix it with the table
        return '%pageid%';
    }

}
