<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;
use dokuwiki\plugin\struct\types\Page;

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
     * @param Page $type
     * @param string $table
     */
    public function __construct($sort, Page $type, $table='') {
        if($type->isMulti()) throw new StructException('PageColumns can not be multi value types!');
        parent::__construct($sort, $type, 0, true, $table);
    }

    public function getColref() {
        throw new StructException('Accessing the colref of a PageColumn makes no sense');
    }

    /**
     * @param bool $enforceSingleColumn ignored
     * @return string
     */
    public function getColName($enforceSingleColumn = true) {
        return 'pid';
    }

    /**
     * @param bool $enforceSingleColumn ignored
     * @return string
     */
    public function getFullColName($enforceSingleColumn = true) {
        $col = $this->getColName($enforceSingleColumn);
        if($this->table) $col = 'data_'.$this->table.'.'.$col;
        return $col;
    }

    /**
     * @return string always '%pageid%'
     */
    public function getLabel() {
        $conf = $this->getType()->getConfig();
        if($conf['usetitles']) {
            return '%title%';
        } else {
            return '%pageid%';
        }
    }

    /**
     * @return string always '%pageid%'
     */
    public function getFullQualifiedLabel() {
        // There is only one pageid for each row because we JOIN on it
        // so we do not prefix it with the table
        return $this->getLabel();
    }

    /**
     * @return string preconfigured label
     */
    public function getTranslatedLabel() {
        /** @var \helper_plugin_struct_config $helper */
        $helper = plugin_load('helper', 'struct_config');
        return $helper->getLang('pagelabel');
    }

}
