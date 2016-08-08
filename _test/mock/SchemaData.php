<?php

namespace dokuwiki\plugin\struct\test\mock;

class AccessTableData extends \dokuwiki\plugin\struct\meta\AccessTableData {



    public function setCorrectTimestamp($page, $ts = null) {
        parent::setCorrectTimestamp($page, $ts);
    }

    public function getDataFromDB() {
        return parent::getDataFromDB();
    }

    public function buildGetDataSQL() {
        return parent::buildGetDataSQL();
    }
}
