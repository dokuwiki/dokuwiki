<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class CSVImporter
 *
 * Imports CSV data into a lookup schema
 *
 * @package dokuwiki\plugin\struct\meta
 */
abstract class CSVImporter {

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

    /** @var  list of headers */
    protected $header;

    /** @var  array list of validation errors */
    protected $errors;

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

        /** @var \helper_plugin_struct_db $db */
        $db = plugin_load('helper', 'struct_db');
        $this->sqlite = $db->getDB(true);
    }

    /**
     * Import the data from file.
     *
     * @throws StructException
     */
    public function import() {
        // Do the import
        $this->readHeaders();
        $this->importCSV();
    }

    /**
     * Read the CSV headers and match it with the Schema columns
     *
     * @return array headers of file
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

        $this->header = $header;
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
     * The errors that occured during validation
     *
     * @return string[] already translated error messages
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Validate a single value
     *
     * @param Column $col the column of that value
     * @param mixed &$rawvalue the value, will be fixed according to the type
     * @return bool true if the data validates, otherwise false
     */
    protected function validateValue(Column $col, &$rawvalue) {
        //by default no validation
        return true;
    }

    /**
     * Read and validate CSV parsed line
     *
     * @param &$line
     */
    protected function readLine(&$line) {
        // prepare values for single value table
        $values = array();
        foreach($this->columns as $i => $column) {
            if(!isset($line[$i])) throw new StructException('Missing field at CSV line %d', $this->line);

            if(!$this->validateValue($column, $line[$i])) return false;

            if($column->isMulti()) {
                // multi values get split on comma
                $line[$i] = array_map('trim', explode(',', $line[$i]));
                $values[] = $line[$i][0];
            } else {
                $values[] = $line[$i];
            }
        }
        //if no ok don't import
        return $values;
    }

    /**
     * INSERT $values into data_* table
     *
     * @param string[] $values
     * @param string $single SQL for single table
     *
     * @return string last_insert_rowid()
     */
    protected function insertIntoSingle($values, $single) {
        $this->sqlite->query($single, $values);
        $res = $this->sqlite->query('SELECT last_insert_rowid()');
        $pid = $this->sqlite->res2single($res);
        $this->sqlite->res_close($res);

        return $pid;
    }

    /**
     * INSERT one row into multi_* table
     *
     * @param string $multi SQL for multi table
     * @param $pid string
     * @param $column string
     * @param $row string
     * @param $value string
     */
    protected function insertIntoMulti($multi, $pid, $column, $row, $value) {
        $this->sqlite->query($multi, array($pid, $column->getColref(), $row + 1, $value));
    }

    /**
     * Save one CSV line into database
     *
     * @param string[] $values parsed line values
     * @param string $single SQL for single table
     * @param string $multi SQL for multi table
     */
    protected function saveLine($values, $line, $single, $multi) {
        // insert into single value table (and create pid)
        $pid = $this->insertIntoSingle($values, $single);

        // insert all the multi values
        foreach($this->columns as $i => $column) {
            if(!$column->isMulti()) continue;
            foreach($line[$i] as $row => $value) {
                $this->insertIntoMulti($multi, $pid, $column, $row, $value);
            }
        }
    }

    /**
     * Imports one line into the schema
     *
     * @param string[] $line the parsed CSV line
     * @param string $single SQL for single table
     * @param string $multi SQL for multi table
     */
    protected function importLine($line, $single, $multi) {
        //read values, false if no validation
        $values = $this->readLine($line);

        if($values) {
            $this->saveLine($values, $line, $single, $multi);
        } else foreach($this->errors as $error) {
            msg($error, -1);
        }
    }
}
