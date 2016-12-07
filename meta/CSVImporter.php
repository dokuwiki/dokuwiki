<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class CSVImporter
 *
 * Imports CSV data into a lookup schema
 *
 * @package dokuwiki\plugin\struct\meta
 */
class CSVImporter {

    /** @var  Schema */
    protected $schema;

    /** @var  resource */
    protected $fh;

    /** @var  \helper_plugin_sqlite */
    protected $sqlite;

    /** @var Column[] The single values to store index => col */
    protected $columns = array();

    /** @var int current line number */
    protected $line = 0;

    /**
     * CSVImporter constructor.
     *
     * @throws StructException
     * @param string $table
     * @param string $file
     */
    public function __construct($table, $file) {
        $this->fh = fopen($file, 'r');
        if(!$this->fh) throw new StructException('Failed to open CSV file for reading');

        $this->schema = new Schema($table);
        if(!$this->schema->getId()) throw new StructException('Schema does not exist');

        if(!$this->schema->isLookup()) throw new StructException('CSV import is only valid for Lookup Schemas');

        /** @var \helper_plugin_struct_db $db */
        $db = plugin_load('helper', 'struct_db');
        $this->sqlite = $db->getDB(true);

        // Do the import
        $this->readHeaders();
        $this->importCSV();
    }

    /**
     * Read the CSV headers and match it with the Schema columns
     */
    protected function readHeaders() {
        $header = fgetcsv($this->fh);
        if(!$header) throw new StructException('Failed to read CSV');
        $this->line++;

        foreach($header as $i => $head) {
            $col = $this->schema->findColumn($head);
            if(!$col) continue;
            if(!$col->isEnabled()) continue;
            $this->columns[$i] = $col;
        }

        if(!$this->columns) {
            throw new StructException('None of the CSV headers matched any of the schema\'s fields');
        }
    }

    /**
     * Creates the insert string for the single value table
     *
     * @return string
     */
    protected function getSQLforAllValues() {
        $colnames = array();
        $placeholds = array();
        foreach($this->columns as $i => $col) {
            $colnames[] = 'col' . $col->getColref();
            $placeholds[] = '?';
        }
        $colnames = join(', ', $colnames);
        $placeholds = join(', ', $placeholds);
        $table = $this->schema->getTable();

        return "INSERT INTO data_$table ($colnames) VALUES ($placeholds)";
    }

    /**
     * Creates the insert string for the multi value table
     *
     * @return string
     */
    protected function getSQLforMultiValue() {
        $table = $this->schema->getTable();
        /** @noinspection SqlResolve */
        return "INSERT INTO multi_$table (pid, colref, row, value) VALUES (?,?,?,?)";
    }

    /**
     * Walks through the CSV and imports
     */
    protected function importCSV() {

        $single = $this->getSQLforAllValues();
        $multi = $this->getSQLforMultiValue();

        $this->sqlite->query('BEGIN TRANSACTION');
        while(($data = fgetcsv($this->fh)) !== false) {
            $this->line++;
            $this->importLine($data, $single, $multi);
        }
        $this->sqlite->query('COMMIT TRANSACTION');
    }

    /**
     * Imports one line into the schema
     *
     * @param string[] $line the parsed CSV line
     * @param string $single SQL for single table
     * @param string $multi SQL for multi table
     */
    protected function importLine($line, $single, $multi) {
        // prepare values for single value table
        $values = array();
        foreach($this->columns as $i => $column) {
            if(!isset($line[$i])) throw new StructException('Missing field at CSV line %d', $this->line);

            if($column->isMulti()) {
                // multi values get split on comma
                $line[$i] = array_map('trim', explode(',', $line[$i]));
                $values[] = $line[$i][0];
            } else {
                $values[] = $line[$i];
            }
        }

        // insert into single value table (and create pid)
        $this->sqlite->query($single, $values);
        $res = $this->sqlite->query('SELECT last_insert_rowid()');
        $pid = $this->sqlite->res2single($res);
        $this->sqlite->res_close($res);

        // insert all the multi values
        foreach($this->columns as $i => $column) {
            if(!$column->isMulti()) continue;
            foreach($line[$i] as $row => $value) {
                $this->sqlite->query($multi, array($pid, $column->getColref(), $row + 1, $value));
            }
        }
    }

}
