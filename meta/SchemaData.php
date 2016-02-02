<?php

namespace plugin\struct\meta;

/**
 * Class SchemaData
 * @package plugin\struct\meta
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
        $table = 'data_' . $this->table;

        $colrefs = array_flip($this->labels);
        $now = time();
        $opt = array($this->page, $now,);
        $multiopts = array();
        $singlecols = 'pid,rev';
        foreach ($data as $colname => $value) {
            if (is_array($value)) {
                foreach ($value as $index => $multivalue) {
                    $multiopts[] = array($colrefs[$colname], $index+1, $multivalue,);
                }
            } else {
                $singlecols .= ",col" . $colrefs[$colname];
                $opt[] = $value;
            }
        }
        $singlesql = "INSERT INTO $table ($singlecols) VALUES (" . trim(str_repeat('?,',count($opt)),',') . ")";
        $multisql = "INSERT INTO multivals (tbl, rev, pid, colref, row, value) VALUES (?,?,?,?,?,?)";

        $this->sqlite->query('BEGIN TRANSACTION');
        if (!$this->sqlite->query($singlesql, $opt)) return false;

        foreach ($multiopts as $multiopt) {
            $multiopt = array_merge(array($table, $now, $this->page,), $multiopt);
            if (!$this->sqlite->query($multisql, $multiopt)) return false;
        }
        $this->sqlite->query('COMMIT TRANSACTION');
        return true;
    }

    /**
     * returns the data saved for the page
     *
     */
    public function getData() {

        $this->setCorrectTimestamp($this->ts);
        $data = $this->getDataFromDB();
        $data = $this->consolidateData($data, $this->labels);

        return $data;
    }

    /**
     * retrieve the data saved for the page from the database. Usually there is no need to call this function.
     * Call @see SchemaData::getData instead.
     *
     * @return array
     */
    public function getDataFromDB() {
        $table = 'data_' . $this->table;

        // prepare column names
        $singles = array();
        $multis = array();
        foreach ($this->columns as $col ){
            if(!$col->isEnabled()) continue;
            if(!$col->getType()->isMulti()) {
                $singles[] = 'col' . $col->getColref();
            } else {
                $multis[] = $col->getColref();
            }
        }
        $colsel = join(',', $singles);

        list($sql, $opt) = $this->buildGetDataSQL($table, $colsel, $multis, $this->page, $this->ts);

        $res = $this->sqlite->query($sql, $opt);
        $data = $this->sqlite->res2arr($res);

        return $data;
    }

    /**
     * @param array $DBdata the data as it is retrieved from the database, i.e. by SchemaData::getDataFromDB
     * @param array $labels A lookup-array of colref => label
     *
     * @return array
     */
    public static function consolidateData($DBdata, $labels) {

        $data = array();
        foreach ($labels as $label) {
            $data[$label] = array();
        }

        foreach ($DBdata as $row) {
            foreach ($row as $column => $value) {
                $data[$labels[substr($column,3)]][] = $value;
            }
        }

        foreach ($data as $column => $values) {
            $values = array_unique($values);
            if (count($values) == 1) {
                $data[$column] = $values[0];
            } else {
                $data[$column] = $values;
            }
        }

        return $data;
    }

    /**
     * Build the sql string and collect the parameters for an sql request to retrieve the data
     *
     * @param string $table  the name of the table
     * @param string $colsel the columns which may only hold 1 value, comma-separated
     * @param array  $multis the colrefs of the columns which may hold more than 1 value
     * @param string $page   the page for which the data is to be retrieved. e.g. $this->page
     * @param string $ts     the exact timestamp of the entry to retrieve, best determined by SchemaData::setCorrectTimestamp
     *
     * @return array
     */
    public static function buildGetDataSQL($table, $colsel, $multis, $page, $ts) {
        $select = "SELECT $colsel";

        $join = '';
        /** @var Column $col */
        foreach ($multis as $colref) {
            $tn = 'M' . $colref;
            $select .= ",$tn.value AS col$colref";
            $join .= "LEFT OUTER JOIN multivals $tn";
            $join .= " ON DATA.pid = $tn.pid AND DATA.rev = $tn.rev";
            $join .= " AND $tn.tbl = '$table' AND $tn.colref = $colref\n";
        }

        $where = "WHERE DATA.pid = ? AND DATA.rev = ?";
        $opt = array($page,$ts,);

        $sql = "$select FROM $table DATA\n$join $where";

        return array($sql, $opt,);
    }

    /**
     * Set $this->ts to an existing timestamp, which is either current timestamp if it exists
     * or the next oldest timestamp that exists. If not timestamp is provided it is the newest timestamp that exists.
     *
     * @param int|null $ts
     */
    public function setCorrectTimestamp($ts = null) {
        $table = 'data_' . $this->table;
        $where = '';
        if ($ts) {
            $where = "WHERE rev <= $ts";
        }
        $sql = "SELECT rev FROM $table $where ORDER BY rev DESC LIMIT 1";
        $res = $this->sqlite->query($sql);
        $this->ts = $this->sqlite->res2single($res);
    }

}
