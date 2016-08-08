<?php

namespace dokuwiki\plugin\struct\test\mock;

use dokuwiki\plugin\struct\meta\Column;


class SchemaDataNoDB extends AccessTableData {


    /** @noinspection PhpMissingParentConstructorInspection
     * @param string $table
     * @param string $pid
     * @param $ts
     */
    public function __construct($table, $pid, $ts) {

        // we do intialization by parent here, because we don't need the whole database behind the class
        $this->schema = new SchemaNoDB($table, $ts);
        $this->pid = $pid;
        $this->ts = $ts;
    }

    public function buildGetDataSQL() {
        return parent::buildGetDataSQL();
    }

    public function setColumns($singles, $multis) {
        $this->schema->columns = array();
        $sort = 0;
        foreach ($singles as $single) {
            $sort += 1;
            $this->schema->columns[] = new Column($sort, new $single(), $sort);
        }
        foreach ($multis as $multi) {
            $sort += 1;
            $this->schema->columns[] = new Column($sort, new $multi(null, null, true), $sort);
        }
    }
}
