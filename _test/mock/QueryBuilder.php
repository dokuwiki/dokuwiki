<?php

namespace dokuwiki\plugin\struct\test\mock;

use \dokuwiki\plugin\struct\meta;

class QueryBuilder extends meta\QueryBuilder {
    public $from;

    public function fixPlaceholders($sql) {
        return parent::fixPlaceholders($sql);
    }

}
