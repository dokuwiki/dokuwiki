<?php

namespace dokuwiki\plugin\struct\test\mock;

use dokuwiki\plugin\struct\meta;
use dokuwiki\plugin\struct\meta\Schema;

abstract class AccessTable extends meta\AccessTable {

    /**
     * @param Schema $schema
     * @param int|string $pid
     * @param int $ts
     * @return meta\AccessTableLookup|AccessTableData
     */
    public static function bySchema(Schema $schema, $pid, $ts = 0) {
        if($schema->isLookup()) {
            return new meta\AccessTableLookup($schema, $pid, $ts); // FIXME not mocked, yet
        } else {
            return new AccessTableData($schema, $pid, $ts);
        }
    }

    public static function byTableName($tablename, $pid, $ts = 0) {
        $schema = new Schema($tablename, $ts);
        return self::bySchema($schema, $pid, $ts); // becuse we have a static call here we can not rely on inheritance
    }

}
