<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class SchemaData
 * @package dokuwiki\plugin\struct\meta
 *
 * This class is for accessing the data stored for a page in a schema
 *
 */
class SchemaData extends Schema {

    protected $page;
    protected $labels = array();

    /**
     * SchemaData constructor
     *
     * @param string $table The table this schema is for
     * @param string $page The page of which the data is for
     * @param int $ts The timestamp for when this schema was valid, 0 for current
     */
    public function __construct($table, $page, $ts) {
        parent::__construct($table, $ts);
        $this->page = $page;
        foreach ($this->columns as $col ){
            $this->labels[$col->getColref()] = $col->getType()->getLabel();
        }
    }

    /**
     * adds an empty data set for this schema and page
     *
     * This is basically a delete for the schema fields of a page
     *
     * @return bool
     */
    public function clearData() {
        $data = array();

        foreach($this->columns as $col) {
            if($col->isMulti()) {
                $data[$col->getLabel()] = array();
            } else {
                $data[$col->getLabel()] = null;
            }
        }

        return $this->saveData($data);
    }

    /**
     * Save the data to the database.
     *
     * We differentiate between single-value-column and multi-value-column by the value to the respective column-name,
     * i.e. depending on if that is a string or an array, respectively.
     *
     * @param array $data typelabel => value for single fields or typelabel => array(value, value, ...) for multi fields
     *
     * @return bool success of saving the data to the database
     */
    public function saveData($data) {
        $stable = 'data_' . $this->table;
        $mtable = 'multi_' . $this->table;

        if($this->ts == 0) throw new StructException("Saving with zero timestamp does not work.");

        $colrefs = array_flip($this->labels);
        $now = $this->ts;
        $opt = array($this->page, $now, 1);
        $multiopts = array();
        $singlecols = 'pid, rev, latest';
        foreach ($data as $colname => $value) {
            if(!isset($colrefs[$colname])) {
                throw new StructException("Unknown column %s in schema.", hsc($colname));
            }

            $singlecols .= ",col" . $colrefs[$colname];
            if (is_array($value)) {
                foreach ($value as $index => $multivalue) {
                    $multiopts[] = array($colrefs[$colname], $index+1, $multivalue,);
                }
                // copy first value to the single column
                if(isset($value[0])) {
                    $opt[] = $value[0];
                } else {
                    $opt[] = null;
                }
            } else {
                $opt[] = $value;
            }
        }
        $singlesql = "INSERT INTO $stable ($singlecols) VALUES (" . trim(str_repeat('?,',count($opt)),',') . ")";
        /** @noinspection SqlResolve */
        $multisql = "INSERT INTO $mtable (rev, pid, colref, row, value) VALUES (?,?,?,?,?)";

        $this->sqlite->query('BEGIN TRANSACTION');

        // remove latest status from previous data
        /** @noinspection SqlResolve */
        $ok = $this->sqlite->query( "UPDATE $stable SET latest = 0 WHERE latest = 1 AND pid = ?",array($this->page));

        // insert single values
        $ok = $ok && $this->sqlite->query($singlesql, $opt);


        // insert multi values
        foreach ($multiopts as $multiopt) {
            $multiopt = array_merge(array($now, $this->page,), $multiopt);
            $ok = $ok && $this->sqlite->query($multisql, $multiopt);
        }

        if (!$ok) {
            $this->sqlite->query('ROLLBACK TRANSACTION');
            return false;
        }
        $this->sqlite->query('COMMIT TRANSACTION');
        return true;
    }

    /**
     * returns the data saved for the page
     *
     * @param bool $skipempty do not return empty or invisible (inpage) fields
     * @return Value[] a list of values saved for the current page
     */
    public function getData($skipempty=false) {
        $this->setCorrectTimestamp($this->page, $this->ts);
        $data = $this->getDataFromDB();
        $data = $this->consolidateData($data, false, $skipempty);
        return $data;
    }

    /**
     * returns the data saved for the page as associative array
     *
     * The array returned is in the same format as used in @see saveData()
     *
     * @param bool $skipempty do not return empty or invisible (inpage) fields
     * @return array
     */
    public function getDataArray($skipempty=false) {
        $this->setCorrectTimestamp($this->page, $this->ts);
        $data = $this->getDataFromDB();
        $data = $this->consolidateData($data, true, $skipempty);
        return $data;
    }

    /**
     * Return the data in pseudo syntax
     */
    public function getDataPseudoSyntax() {
        $result = '';
        $data = $this->getDataArray();
        foreach($data as $key => $value) {
            $key = $this->table . ".$key";
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
     * @param bool $skipemtpy skip empty or invisible (inpage) fields from being returned at all
     * @return array|Value[]
     */
    protected function consolidateData($DBdata, $asarray = false, $skipemtpy=false) {
        $data = array();

        $sep = Search::CONCAT_SEPARATOR;

        foreach($this->getColumns() as $col) {
            if(!$col->isEnabled()) continue;

            // if no data saved, yet return empty strings
            if($DBdata) {
                $val = $DBdata[0]['col'.$col->getColref()];
            } else {
                $val = '';
            }

            // multi val data is concatenated
            if($col->isMulti()) {
                $val = explode($sep, $val);
                $val = array_filter($val);
            }

            if($skipemtpy && ($val === '' || $val == array())) continue;
            if($skipemtpy && !$col->isVisibleInPage()) continue;

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
        $stable = 'data_' . $this->table;
        $mtable = 'multi_' . $this->table;

        $QB = new QueryBuilder();
        $QB->addTable($stable, 'DATA');
        $QB->addSelectColumn('DATA', 'pid', 'PID');
        $QB->addGroupByStatement('DATA.pid');

        foreach($this->columns as $col) {
            if(!$col->isEnabled()) continue;

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

        $pl = $QB->addValue($this->page);
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
     */
    protected function setCorrectTimestamp($page, $ts = null) {
        $table = 'data_' . $this->table;
        $where = "WHERE pid = '$page'";
        if ($ts) {
            $where .= " AND rev <= $ts";
        }
        /** @noinspection SqlResolve */
        $sql = "SELECT rev FROM $table $where ORDER BY rev DESC LIMIT 1";
        $res = $this->sqlite->query($sql);
        $this->ts = $this->sqlite->res2single($res);
    }

}
