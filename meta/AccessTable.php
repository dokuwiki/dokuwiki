<?php

namespace dokuwiki\plugin\struct\meta;

abstract class AccessTable {

    /** @var  Schema */
    protected $schema;
    protected $pid;
    protected $labels = array();
    protected $ts     = 0;
    /** @var \helper_plugin_sqlite */
    protected $sqlite;

    // options on how to retrieve data
    protected $opt_skipempty = false;

    /**
     * Factory Method to access a data or lookup table
     *
     * @param Schema $schema schema to load
     * @param string|int $pid Page or row id to access
     * @param int $ts Time at which the data should be read or written, 0 for now
     * @return AccessTableData|AccessTableLookup
     */
    public static function bySchema(Schema $schema, $pid, $ts = 0) {
        if($schema->isLookup()) {
            return new AccessTableLookup($schema, $pid, $ts);
        } else {
            return new AccessTableData($schema, $pid, $ts);
        }
    }

    /**
     * Factory Method to access a data or lookup table
     *
     * @param string $tablename schema to load
     * @param string|int $pid Page or row id to access
     * @param int $ts Time at which the data should be read or written, 0 for now
     * @return AccessTableData|AccessTableLookup
     */
    public static function byTableName($tablename, $pid, $ts = 0) {
        $schema = new Schema($tablename, $ts);
        return self::bySchema($schema, $pid, $ts);
    }

    /**
     * AccessTable constructor
     *
     * @param Schema $schema The schema valid at $ts
     * @param string|int $pid
     * @param int $ts Time at which the data should be read or written, 0 for now
     */
    public function __construct(Schema $schema, $pid, $ts = 0) {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();
        if(!$this->sqlite) {
            throw new StructException('Sqlite plugin required');
        }

        if(!$schema->getId()) {
            throw new StructException('Schema does not exist. Only data of existing schemas can be accessed');
        }

        $this->schema = $schema;
        $this->pid = $pid;
        $this->setTimestamp($ts);
        foreach($this->schema->getColumns() as $col) {
            $this->labels[$col->getColref()] = $col->getType()->getLabel();
        }
    }

    /**
     * gives access to the schema
     *
     * @return Schema
     */
    public function getSchema() {
        return $this->schema;
    }

    /**
     * Should remove the current data, by either deleting or ovewriting it
     *
     * @return bool if the delete succeeded
     */
    abstract public function clearData();

    /**
     * Save the data to the database.
     *
     * We differentiate between single-value-column and multi-value-column by the value to the respective column-name,
     * i.e. depending on if that is a string or an array, respectively.
     *
     * @param array $data typelabel => value for single fields or typelabel => array(value, value, ...) for multi fields
     * @return bool success of saving the data to the database
     */
    abstract public function saveData($data);

    /**
     * Should empty or invisible (inpage) fields be returned?
     *
     * Defaults to false
     *
     * @param null|bool $set new value, null to read only
     * @return bool current value (after set)
     */
    public function optionSkipEmpty($set = null) {
        if(!is_null($set)) {
            $this->opt_skipempty = $set;
        }
        return $this->opt_skipempty;
    }

    /**
     * Get the value of a single column
     *
     * @param Column $column
     * @return Value|null
     */
    public function getDataColumn($column) {
        $data = $this->getData();
        foreach($data as $value) {
            if($value->getColumn() == $column) {
                return $value;
            }
        }
        return null;
    }

    /**
     * returns the data saved for the page
     *
     * @return Value[] a list of values saved for the current page
     */
    public function getData() {
        $data = $this->getDataFromDB();
        $data = $this->consolidateData($data, false);
        return $data;
    }

    /**
     * returns the data saved for the page as associative array
     *
     * The array returned is in the same format as used in @see saveData()
     *
     * It always returns raw Values!
     *
     * @return array
     */
    public function getDataArray() {
        $data = $this->getDataFromDB();
        $data = $this->consolidateData($data, true);
        return $data;
    }

    /**
     * Return the data in pseudo syntax
     */
    public function getDataPseudoSyntax() {
        $result = '';
        $data = $this->getDataArray();
        foreach($data as $key => $value) {
            $key = $this->schema->getTable() . ".$key";
            if(is_array($value)) $value = join(', ', $value);
            $result .= sprintf("% -20s : %s\n", $key, $value);
        }
        return $result;
    }

    /**
     * retrieve the data saved for the page from the database. Usually there is no need to call this function.
     * Call @see SchemaData::getData instead.
     */
    protected function getDataFromDB() {
        list($sql, $opt) = $this->buildGetDataSQL();

        $res = $this->sqlite->query($sql, $opt);
        $data = $this->sqlite->res2arr($res);

        return $data;
    }

    /**
     * Creates a proper result array from the database data
     *
     * @param array $DBdata the data as it is retrieved from the database, i.e. by SchemaData::getDataFromDB
     * @param bool $asarray return data as associative array (true) or as array of Values (false)
     * @return array|Value[]
     */
    protected function consolidateData($DBdata, $asarray = false) {
        $data = array();

        $sep = Search::CONCAT_SEPARATOR;

        foreach($this->schema->getColumns(false) as $col) {

            // if no data saved yet, return empty strings
            if($DBdata) {
                $val = $DBdata[0]['out' . $col->getColref()];
            } else {
                $val = '';
            }

            // multi val data is concatenated
            if($col->isMulti()) {
                $val = explode($sep, $val);
                $val = array_filter($val);
            }

            $value = new Value($col, $val);

            if($this->opt_skipempty && $value->isEmpty()) continue;
            if($this->opt_skipempty && !$col->isVisibleInPage()) continue; //FIXME is this a correct assumption?

            // for arrays, we return the raw value only
            if($asarray) {
                $data[$col->getLabel()] = $value->getRawValue();
            } else {
                $data[] = $value;
            }
        }

        return $data;
    }

    /**
     * Builds the SQL statement to select the data for this page and schema
     *
     * @return array Two fields: the SQL string and the parameters array
     */
    protected function buildGetDataSQL() {
        $sep = Search::CONCAT_SEPARATOR;
        $stable = 'data_' . $this->schema->getTable();
        $mtable = 'multi_' . $this->schema->getTable();

        $QB = new QueryBuilder();
        $QB->addTable($stable, 'DATA');
        $QB->addSelectColumn('DATA', 'pid', 'PID');
        $QB->addGroupByStatement('DATA.pid');

        foreach($this->schema->getColumns(false) as $col) {

            $colref = $col->getColref();
            $colname = 'col' . $colref;
            $outname = 'out' . $colref;

            if($col->getType()->isMulti()) {
                $tn = 'M' . $colref;
                $QB->addLeftJoin(
                    'DATA',
                    $mtable,
                    $tn,
                    "DATA.pid = $tn.pid AND DATA.rev = $tn.rev AND $tn.colref = $colref"
                );
                $col->getType()->select($QB, $tn, 'value', $outname);
                $sel = $QB->getSelectStatement($outname);
                $QB->addSelectStatement("GROUP_CONCAT($sel, '$sep')", $outname);
            } else {
                $col->getType()->select($QB, 'DATA', $colname, $outname);
                $QB->addGroupByStatement($outname);
            }
        }

        $pl = $QB->addValue($this->pid);
        $QB->filters()->whereAnd("DATA.pid = $pl");
        $pl = $QB->addValue($this->getLastRevisionTimestamp());
        $QB->filters()->whereAnd("DATA.rev = $pl");

        return $QB->getSQL();
    }

    /**
     * @param int $ts
     */
    public function setTimestamp($ts) {
        if($ts && $ts < $this->schema->getTimeStamp()) {
            throw new StructException('Given timestamp is not valid for current Schema');
        }

        $this->ts = $ts;
    }

    /**
     * Return the last time an edit happened for this table for the currently set
     * time and pid. When the current timestamp is 0, the newest revision is
     * returned. Used in @see buildGetDataSQL()
     *
     * @return int
     */
    abstract protected function getLastRevisionTimestamp();

    /**
     * Check if the given data validates against the current types.
     *
     * @param array $data
     * @return AccessDataValidator
     */
    public function getValidator($data) {
        return new AccessDataValidator($this, $data);
    }
}


