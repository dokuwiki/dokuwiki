<?php

namespace dokuwiki\plugin\struct\test\mock;

class SchemaNoDB extends \dokuwiki\plugin\struct\meta\Schema {

    public $columns = array();

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($table, $ts, $islookup = false) {
        $this->table = $table;
        $this->ts = $ts;
        $this->islookup = $islookup;
    }

}
