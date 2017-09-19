<?php

namespace dokuwiki\plugin\struct\meta;

class CSVLookupImporter extends CSVImporter {

    /**
     * Check if schema is lookup
     *
     * @throws StructException
     * @param string $table
     * @param string $file
     */
    public function __construct($table, $file) {
        parent::__construct($table, $file);

        if(!$this->schema->isLookup()) throw new StructException($table.' is not lookup schema');
    }

}
