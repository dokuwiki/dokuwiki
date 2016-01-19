<?php

namespace plugin\struct\meta;

/**
 * Class SchemaBuilder
 *
 * This class builds and updates the schema definitions for our tables. This includes CREATEing and ALTERing
 * the actual data tables as well as updating the meta information in our meta data tables.
 *
 * To use, simply instantiate a new object of the Builder and run the build() method on it.
 *
 * Note: even though data tables use a data_ prefix in the database, this prefix is internal only and should
 *       never be passed as $table anywhere!
 *
 * @package plugin\struct\meta
 */
class SchemaBuilder {

    /**
     * @var array The posted new data for the schema
     * @see Schema::AdminEditor()
     */
    protected $data = array();

    /**
     * @var string The table name associated with the schema
     */
    protected $table = '';

    /**
     * @var Schema the previously valid schema for this table
     */
    protected $oldschema;

    /** @var int the ID of the newly created schema */
    protected $newschemaid = 0;

    /** @var \helper_plugin_sqlite|null  */
    protected $sqlite;

    /**
     * SchemaBuilder constructor.
     *
     * @param string $table The table's name
     * @param array $data The defining of the table (basically what get's posted in the schema editor form)
     * @see Schema::AdminEditor()
     */
    public function __construct($table, $data) {
        $this->table = $table;
        $this->data = $data;
        $this->oldschema = new Schema($table);

        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();
    }

    /**
     * Create the new schema
     *
     * @return bool|int the new schema id on success
     */
    public function build() {
        $this->sqlite->query('BEGIN TRANSACTION');

        // create the data table if new schema
        if(!$this->oldschema->getId()) {
            $ok = $this->newDataTable();
            if(!$ok) return false;
        }

        // create a new schema
        if(!$this->newSchema()) return false;

        // update column info
        if(!$this->updateColumns()) return false;
        if(!$this->addColumns()) return false;

        $this->sqlite->query('COMMIT TRANSACTION');

        return $this->newschemaid;
    }

    /**
     * Creates a new schema
     *
     * @todo use checksum or other heuristic to see if we really need a new schema OTOH we probably need one nearly always!?
     */
    protected function newSchema() {
        $sql = "INSERT INTO schemas (tbl, ts) VALUES (?, ?)";
        $this->sqlite->query($sql, $this->table, time());
        $res = $this->sqlite->query('SELECT last_insert_rowid()');
        $this->newschemaid = $this->sqlite->res2single($res);
        $this->sqlite->res_close($res);
        if(!$this->newschemaid) return false;
        return true;
    }

    /**
     * Updates all the existing column infos and adds them to the new schema
     */
    protected function updateColumns() {
        foreach($this->oldschema->getColumns() as $column) {
            $oldEntry = $column->getType()->getAsEntry();
            $oldTid   = $column->getTid();
            $newEntry = $oldEntry;
            $newTid   = $oldTid;
            $enabled  = true;
            $sort = $column->getSort();
            if(isset($this->data['cols'][$column->getColref()])){
                // todo I'm not too happy with this hardcoded here - we should probably have a list of fields at one place
                $newEntry['config'] = $this->data['cols'][$column->getColref()]['config'];
                $newEntry['label'] = $this->data['cols'][$column->getColref()]['label'];
                $newEntry['ismulti'] = $this->data['cols'][$column->getColref()]['multi'];
                $newEntry['class'] = $this->data['cols'][$column->getColref()]['class'];
                $sort = $this->data['cols'][$column->getColref()]['sort'];

                // when the type definition has changed, we create a new one
                if(array_diff_assoc($oldEntry, $newEntry)) {
                    $ok = $this->sqlite->storeEntry('types', $newEntry);
                    if(!$ok) return false;
                    $res = $this->sqlite->query('SELECT last_insert_rowid()');
                    if(!$res) return false;
                    $newTid = $this->sqlite->res2single($res);
                    $this->sqlite->res_close($res);
                }
            } else {
                $enabled = false; // no longer there FIXME this assumes we remove the entry from the form completely. We might not want to do that
            }

            // add this type to the schema columns
            $schemaEntry = array(
                'sid' => $this->newschemaid,
                'colref' => $column->getColref(),
                'enabled' => $enabled,
                'tid' => $newTid,
                'sort' => $sort
            );
            $ok = $this->sqlite->storeEntry('schema_cols', $schemaEntry);
            if(!$ok) return false;
        }
        return true;
    }

    /**
     * Adds new columns to the new schema
     *
     * @return bool
     */
    protected function addColumns() {
        if(!isset($this->data['new'])) return true;

        $colref = count($this->oldschema->getColumns())+1;

        foreach($this->data['new'] as $column) {
            // todo this duplicates the hardcoding as in  the function above
            $newEntry = array();
            $newEntry['config'] = $column['config'];
            $newEntry['label'] = $column['label'];
            $newEntry['ismulti'] = $column['multi'];
            $newEntry['class'] = $column['class'];
            $sort = $column['sort'];
            $enabled = true;

            // only save if the column got a name
            if(!$newEntry['label']) continue;

            // add new column to the data table
            if(!$this->addDataTableColumn($colref)) {
                return false;
            }

            // save the type
            $ok = $this->sqlite->storeEntry('types', $newEntry);
            if(!$ok) return false;
            $res = $this->sqlite->query('SELECT last_insert_rowid()');
            if(!$res) return false;
            $newTid = $this->sqlite->res2single($res);
            $this->sqlite->res_close($res);


            // add this type to the schema columns
            $schemaEntry = array(
                'sid' => $this->newschemaid,
                'colref' => $colref,
                'enabled' => $enabled,
                'tid' => $newTid,
                'sort' => $sort
            );
            $ok = $this->sqlite->storeEntry('schema_cols', $schemaEntry);
            if(!$ok) return false;
            $colref++;
        }

        return true;
    }

    /**
     * Create a completely new data table with columns yet
     *
     * @todo how do we want to handle indexes?
     * @return bool
     */
    protected function newDataTable() {
        $tbl = 'data_' . $this->table;

        $sql = "CREATE TABLE $tbl (
                    pid NOT NULL,
                    rev INTEGER NOT NULL,
                    PRIMARY KEY(pid, rev)
                )";

        return (bool) $this->sqlite->query($sql);
    }

    /**
     * Add an additional column to the existing data table
     *
     * @param int $index the new column index to add
     * @return bool
     */
    protected function addDataTableColumn($index) {
        $tbl = 'data_' . $this->table;
        $sql = " ALTER TABLE $tbl ADD COLUMN col$index DEFAULT ''";
        if(! $this->sqlite->query($sql)) {
            return false;
        }
        return true;
    }

}
