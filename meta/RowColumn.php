<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\Decimal;

/**
 * Like a Page Column but for Lookups using a decimal type
 */
class RowColumn extends PageColumn {

    /** @noinspection PhpMissingParentConstructorInspection
     * @param int $sort
     * @param Decimal $type
     * @param string $table
     */
    public function __construct($sort, Decimal $type, $table) {
        if($type->isMulti()) throw new StructException('RowColumns can not be multi value types!');
        Column::__construct($sort, $type, 0, true, $table);
    }

    /**
     * @return string always '%rowid%'
     */
    public function getLabel() {
        return '%rowid%';
    }

    /**
     * @return string preconfigured label
     */
    public function getTranslatedLabel() {
        /** @var \helper_plugin_struct_config $helper */
        $helper = plugin_load('helper', 'struct_config');
        return $helper->getLang('rowlabel');
    }
}
