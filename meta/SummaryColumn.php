<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\AbstractBaseType;
use dokuwiki\plugin\struct\types\Summary;

/**
 * Class SummaryColumn
 *
 * Just like a column, but does not reference one of the col* data columns but the pid column.
 *
 * @package dokuwiki\plugin\struct\meta
 */
class SummaryColumn extends Column {

    /**
     * PageColumn constructor.
     *
     * @param int $sort
     * @param PageMeta $type
     * @param string $table
     */
    public function __construct($sort, Summary $type, $table='') {
        if($type->isMulti()) throw new StructException('SummaryColumns can not be multi value types!');
        parent::__construct($sort, $type, 0, true, $table);
        $this->getType()->setContext($this);
    }

    public function getColref() {
        throw new StructException('Accessing the colref of a SummaryColumn makes no sense');
    }

    /**
     * @param bool $enforceSingleColumn ignored
     * @return string
     */
    public function getColName($enforceSingleColumn = true) {
        return 'lastsummary';
    }

    /**
     * @param bool $enforceSingleColumn ignored
     * @return string
     */
    public function getFullColName($enforceSingleColumn = true) {
        $col = 'titles.'.$this->getColName($enforceSingleColumn);
        return $col;
    }

    /**
     * @return string always '%lastsummary%'
     */
    public function getLabel() {
        return '%lastsummary%';
    }

    /**
     * @return string always '%lastsummary%'
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
        return $helper->getLang('summarylabel');
    }

}
