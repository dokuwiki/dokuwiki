<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\User;

/**
 * Class UserColumn
 *
 * Just like a column, but does not reference one of the col* data columns but the lasteditor column in the titles table.
 *
 * @package dokuwiki\plugin\struct\meta
 */
class UserColumn extends Column {

    /**
     * PageColumn constructor.
     *
     * @param int $sort
     * @param User $type
     * @param string $table
     */
    public function __construct($sort, User $type, $table='') {
        if($type->isMulti()) throw new StructException('UserColumns can not be multi value types!');
        parent::__construct($sort, $type, 0, true, $table);
        $this->getType()->setContext($this);
    }

    public function getColref() {
        throw new StructException('Accessing the colref of a UserColumn makes no sense');
    }

    /**
     * @param bool $enforceSingleColumn ignored
     * @return string
     */
    public function getColName($enforceSingleColumn = true) {
        return 'lasteditor';
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
     * @return string always '%lasteditor%'
     */
    public function getLabel() {
        return '%lasteditor%';
    }

    /**
     * @return string always '%lasteditor%'
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
        return $helper->getLang('userlabel');
    }

}
