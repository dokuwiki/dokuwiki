<?php

namespace dokuwiki\plugin\struct\test\mock;

use \dokuwiki\plugin\struct\meta;

class SearchConfig extends meta\SearchConfig {
    public function applyFilterVars($filter) {
        return parent::applyFilterVars($filter);
    }

    public function determineCacheFlag($filters) {
        return parent::determineCacheFlag($filters);
    }

}
