<?php

namespace plugin\struct\test\mock;

class SchemaDataNoDB extends \plugin\struct\meta\SchemaData {

    public function __construct($table, $page, $ts) {
        // we do intialization by parent here, because we don't need the whole database behind the class
        $this->page = $page;
        $this->table = $table;
        $this->ts = $ts;
    }

    public function buildGetDataSQL($singles, $multis) {
        return parent::buildGetDataSQL($singles, $multis);
    }
}
