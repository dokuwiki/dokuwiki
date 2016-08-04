<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class LookupTable
 *
 * An AggregationTable for editing lookup tables
 *
 * @package dokuwiki\plugin\struct\meta
 */
class LookupTable extends AggregationTable {

    /**
     * @var bool skip full table when no results found
     */
    protected $simplenone = false;

    /**
     * Adds additional info to document and renderer in XHTML mode
     *
     * We add the schema name as data attribute
     *
     * @see finishScope()
     */
    protected function startScope() {
        if($this->mode != 'xhtml') return;

        $table = $this->columns[0]->getTable();

        // wrapping div
        $this->renderer->doc .= "<div class=\"structaggregation structlookup\" data-schema=\"$table\">";

        // unique identifier for this aggregation
        $this->renderer->info['struct_table_hash'] = md5(var_export($this->data, true));
    }

    /**
     * We do not output a row for empty tables
     */
    protected function renderEmptyResult() {
    }

}
