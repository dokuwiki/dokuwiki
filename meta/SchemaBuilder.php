<?php

namespace plugin\struct\meta;

class SchemaBuilder {

    /**
     * @var array The posted new data for the schema
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
     * @param string $table
     * @param array $data
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

        // create or update the data table
        if($this->oldschema->getId()) {
            $ok = $this->updateDataTable();
        } else {
            $ok = $this->newDataTable();
        }
        if(!$ok) return false;

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
                $enabled = false; // no longer there FIXME this assumes we remove the entry from the from completely. We might not want to do that
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
     * Create a completely new data table
     *
     * @todo how do we want to handle indexes?
     * @return bool
     */
    protected function newDataTable() {
        $tbl = 'data_' . $this->table;
        $cols = count($this->data['new']); // number of columns in the schema

        $sql = "CREATE TABLE $tbl (
                    pid NOT NULL,
                    rev INTEGER NOT NULL,\n";
        for($i = 1; $i <= $cols; $i++) {
            $sql .= "col$i DEFAULT '',\n";
        }
        $sql .= "PRIMARY KEY(pid, rev) )";

        return (bool) $this->sqlite->query($sql);
    }

    /**
     * Add additional columns to an existing data table
     *
     * @return bool
     */
    protected function updateDataTable() {
        $tbl = 'data_' . $this->table;
        $oldcols = count($this->oldschema->getColumns()); // number of columns in the old schema
        $newcols = count($this->data['new']); // number of *new* columns in the schema

        for($i = $oldcols+1; $i <= $oldcols + $newcols; $i++) {
            $sql = " ALTER TABLE $tbl ADD COLUMN col$i DEFAULT ''";
            if(! $this->sqlite->query($sql)) {
                return false;
            }
        }

        return true;
    }

}
