<?php

namespace dokuwiki\plugin\struct\test\mock;

use \dokuwiki\plugin\struct\meta;

class QueryBuilder extends meta\QueryBuilder {
    public $from;

    public function fixPlaceholders($sql) {
        return parent::fixPlaceholders($sql);
    }

    /**
     * for debugging where statements
     *
     * @return array ($sql, $opts)
     */
    public function getWhereSQL() {
        return $this->fixPlaceholders($this->filters()->toSQL());
    }
}
