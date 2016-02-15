<?php

namespace plugin\struct\meta;

use plugin\struct\types\AbstractBaseType;

/**
 * Class PageColumn
 *
 * Just like a column, but does not reference one of the col* data columns but the pid column.
 *
 * @package plugin\struct\meta
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
        parent::__construct($sort, $type, 0, true, $table);
    }

    public function getColref() {
        throw new StructException('Accessing the colref of a PageColumn makes no sense');
    }

}
