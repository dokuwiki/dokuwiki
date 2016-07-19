<?php

namespace dokuwiki\plugin\struct\test\mock;

class SchemaDataNoDB extends \dokuwiki\plugin\struct\meta\SchemaData {

    public function __construct($table, $page, $ts) {
        // we do intialization by parent here, because we don't need the whole database behind the class
        $this->page = $page;
        $this->table = $table;
        $this->ts = $ts;
    }

    public function buildGetDataSQL() {
        return parent::buildGetDataSQL();
    }
}
