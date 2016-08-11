<?php

namespace dokuwiki\plugin\struct\meta;

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
 * @package dokuwiki\plugin\struct\meta
 */
class SchemaBuilder {

    /**
     * @var array The posted new data for the schema
     * @see Schema::AdminEditor()
     */
    protected $data = array();

    protected $user;

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

    /** @var \helper_plugin_struct_db */
    protected $helper;

    /** @var \helper_plugin_sqlite|null  */
    protected $sqlite;

    /** @var int the time for which this schema should be created - default to time() can be overriden for tests */
    protected $time = 0;

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

        $this->helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $this->helper->getDB();
    }

    /**
     * Create the new schema
     *
     * @param int $time when to create this schema 0 for now
     * @return bool|int the new schema id on success
     */
    public function build($time=0) {
        $this->time = $time;
        $this->fixLabelUniqueness();

        $this->sqlite->query('BEGIN TRANSACTION');
        $ok = true;
        // create the data table if new schema
        if(!$this->oldschema->getId()) {
            $ok = $this->newDataTable();
        }

        // create a new schema
        $ok = $ok && $this->newSchema();

        // update column info
        $ok = $ok && $this->updateColumns();
        $ok = $ok && $this->addColumns();

        if (!$ok) {
            $this->sqlite->query('ROLLBACK TRANSACTION');
            return false;
        }
        $this->sqlite->query('COMMIT TRANSACTION');

        return $this->newschemaid;
    }

    /**
     * Makes sure all labels in the schema to save are unique
     */
    protected function fixLabelUniqueness() {
        $labels = array();

        if(isset($this->data['cols'])) foreach($this->data['cols'] as $idx => $column) {
            $this->data['cols'][$idx]['label'] = $this->fixLabel($column['label'], $labels);
        }

        if(isset($this->data['new'])) foreach($this->data['new'] as $idx => $column) {
            $this->data['new'][$idx]['label'] = $this->fixLabel($column['label'], $labels);
        }
    }

    /**
     * Creates a unique label from the given one
     *
     * @param string $wantedlabel
     * @param array $labels list of already assigned labels (will be filled)
     * @return string
     */
    protected function fixLabel($wantedlabel, &$labels) {
        $wantedlabel = trim($wantedlabel);
        $fixedlabel = $wantedlabel;
        $idx = 1;
        while(isset($labels[utf8_strtolower($fixedlabel)])) {
            $fixedlabel = $wantedlabel.$idx++;
        }
        // did we actually do a rename? apply it.
        if($fixedlabel != $wantedlabel) {
            msg(sprintf($this->helper->getLang('duplicate_label'), $wantedlabel, $fixedlabel), -1);
            $this->data['cols']['label'] = $fixedlabel;
        }
        $labels[utf8_strtolower($fixedlabel)] = 1;
        return $fixedlabel;
    }

    /**
     * Creates a new schema
     *
     * @todo use checksum or other heuristic to see if we really need a new schema OTOH we probably need one nearly always!?
     */
    protected function newSchema() {
        if(!$this->time) $this->time = time();

        $sql = "INSERT INTO schemas (tbl, ts, user) VALUES (?, ?, ?)";
        $this->sqlite->query($sql, $this->table, $this->time, blank($this->user) ? $_SERVER['REMOTE_USER'] : $this->user);
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
            $sort = $column->getSort();
            if(isset($this->data['cols'][$column->getColref()])){
                // todo I'm not too happy with this hardcoded here - we should probably have a list of fields at one place
                $newEntry['config'] = $this->data['cols'][$column->getColref()]['config'];
                $newEntry['label'] = $this->data['cols'][$column->getColref()]['label'];
                $newEntry['ismulti'] = $this->data['cols'][$column->getColref()]['ismulti'];
                $newEntry['class'] = $this->data['cols'][$column->getColref()]['class'];
                $sort = $this->data['cols'][$column->getColref()]['sort'];
                $enabled = (bool) $this->data['cols'][$column->getColref()]['isenabled'];

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
                $enabled = false; // no longer there for some reason
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
            if(!$column['isenabled']) continue; // we do not add a disabled column

            // todo this duplicates the hardcoding as in  the function above
            $newEntry = array();
            $newEntry['config'] = $column['config'];
            $newEntry['label'] = $column['label'];
            $newEntry['ismulti'] = $column['ismulti'];
            $newEntry['class'] = $column['class'];
            $sort = $column['sort'];


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
                'enabled' => true,
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
     * Create a completely new data table with no columns yet also create the appropriate
     * multi value table for the schema
     *
     * @todo how do we want to handle indexes?
     * @return bool
     */
    protected function newDataTable() {
        $ok = true;

        $tbl = 'data_' . $this->table;
        $sql = "CREATE TABLE $tbl (
                    pid NOT NULL,
                    rev INTEGER NOT NULL,
                    latest BOOLEAN NOT NULL DEFAULT 0,
                    PRIMARY KEY(pid, rev)
                )";
        $ok = $ok && (bool) $this->sqlite->query($sql);

        $tbl = 'multi_' . $this->table;
        $sql = "CREATE TABLE $tbl (
                    colref INTEGER NOT NULL,
                    pid NOT NULL,
                    rev INTEGER NOT NULL,
                    row INTEGER NOT NULL,
                    value,
                    PRIMARY KEY(colref, pid, rev, row)
                );";
        $ok = $ok && (bool) $this->sqlite->query($sql);

        return $ok;
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
