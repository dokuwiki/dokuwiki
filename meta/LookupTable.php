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
        // unique identifier for this aggregation
        $this->renderer->info['struct_table_hash'] = md5(var_export($this->data, true));

        if($this->mode != 'xhtml') return;

        $table = $this->columns[0]->getTable();

        $config = $this->searchConfig->getConf();
        if(isset($config['filter'])) unset($config['filter']);
        $config = hsc(json_encode($config));

        // wrapping div
        $this->renderer->doc .= "<div class=\"structaggregation structlookup\" data-schema=\"$table\" data-searchconf=\"$config\">";

        // unique identifier for this aggregation
        $this->renderer->info['struct_table_hash'] = md5(var_export($this->data, true));
    }

    /**
     * We do not output a row for empty tables
     */
    protected function renderEmptyResult() {
    }

    /**
     * Renders the first result row and returns it
     *
     * Only used for rendering new rows via JS (where the first row is the only one)
     *
     * @return string
     */
    public function getFirstRow() {
        $this->renderResultRow(0, $this->result[0]);
        return $this->renderer->doc;
    }

    /**
     * @inheritDoc
     */
    public function render() {
        if(!$this->searchConfig->getSchemas()[0]->isLookup()) {
            msg($this->helper->getLang('no_lookup_for_page'), -1);
            return;
        }
        parent::render();
    }

}
