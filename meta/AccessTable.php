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

    // options on how to retieve data
    protected $opt_skipempty = false;
    protected $opt_rawvalue  = false;

    /**
     * Factory Method to access a data or lookup table
     *
     * @param Schema $schema schema to load
     * @param string|int $pid Page or row id to access
     * @return AccessTableData|AccessTableLookup
     */
    public static function bySchema(Schema $schema, $pid) {
        if($schema->isLookup()) {
            return new AccessTableLookup($schema, $pid);
        } else {
            return new AccessTableData($schema, $pid);
        }
    }

    /**
     * Factory Method to access a data or lookup table
     *
     * @param string $tablename schema to load
     * @param string|int $pid Page or row id to access
     * @param int $ts from when is the schema to access?
     * @return AccessTableData|AccessTableLookup
     */
    public static function byTableName($tablename, $pid, $ts = 0) {
        $schema = new Schema($tablename, $ts);
        return self::bySchema($schema, $pid);
    }

    /**
     * AccessTable constructor
     *
     * @param Schema $schema
     * @param string $pid
     */
    public function __construct(Schema $schema, $pid) {
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
        $this->ts = $this->schema->getTimeStamp();
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
     * Should the values be returned raw or are complex returns okay?
     *
     * Defaults to false = complex values okay
     *
     * @param null|bool $set new value, null to read only
     * @return bool current value (after set)
     */
    public function optionRawValue($set = null) {
        if(!is_null($set)) {
            $this->opt_rawvalue = $set;
        }
        return $this->opt_rawvalue;
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
        $this->setCorrectTimestamp($this->pid, $this->ts);
        $data = $this->getDataFromDB();
        $data = $this->consolidateData($data, false);
        return $data;
    }

    /**
     * returns the data saved for the page as associative array
     *
     * The array returned is in the same format as used in @see saveData()
     *
     * @return array
     */
    public function getDataArray() {
        $this->setCorrectTimestamp($this->pid, $this->ts);
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

            // if no data saved, yet return empty strings
            if($DBdata) {
                $val = $DBdata[0]['col'.$col->getColref()];
            } else {
                $val = '';
            }

            // multi val data is concatenated
            if($col->isMulti()) {
                $val = explode($sep, $val);
                if($this->opt_rawvalue) {
                    $val = array_map(
                        function ($val) use ($col) { // FIXME requires PHP 5.4+
                            return $col->getType()->rawValue($val);
                        },
                        $val
                    );
                }
                $val = array_filter($val);
            } else {
                if($this->opt_rawvalue) {
                    $val = $col->getType()->rawValue($val);
                }
            }

            if($this->opt_skipempty && ($val === '' || $val == array())) continue;
            if($this->opt_skipempty && !$col->isVisibleInPage()) continue;

            if($asarray) {
                $data[$col->getLabel()] = $val;
            } else {
                $data[] = new Value($col, $val);
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
            $colname = 'col'.$colref;

            if($col->getType()->isMulti()) {
                $tn = 'M' . $colref;
                $QB->addLeftJoin(
                    'DATA',
                    $mtable,
                    $tn,
                    "DATA.pid = $tn.pid AND DATA.rev = $tn.rev AND $tn.colref = $colref"
                );
                $col->getType()->select($QB, $tn, 'value', $colname);
                $sel = $QB->getSelectStatement($colname);
                $QB->addSelectStatement("GROUP_CONCAT($sel, '$sep')", $colname);
            } else {
                $col->getType()->select($QB, 'DATA', $colname, $colname);
                $QB->addGroupByStatement($colname);
            }
        }

        $pl = $QB->addValue($this->pid);
        $QB->filters()->whereAnd("DATA.pid = $pl");
        $pl = $QB->addValue($this->ts);
        $QB->filters()->whereAnd("DATA.rev = $pl");

        return $QB->getSQL();
    }

    /**
     * Set $this->ts to an existing timestamp, which is either current timestamp if it exists
     * or the next oldest timestamp that exists. If not timestamp is provided it is the newest timestamp that exists.
     *
     * @param          $page
     * @param int|null $ts
     * @fixme clear up description
     */
    abstract protected function setCorrectTimestamp($page, $ts = null);
}


