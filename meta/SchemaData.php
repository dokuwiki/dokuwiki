<?php

namespace plugin\struct\meta;

/**
 * Class SchemaData
 * @package plugin\struct\meta
 *
 * This class is for accessing the data stored for a page in a schema
 *
 * @todo handle saving data
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
     * @param array $DBdata
     * @param array $labels
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
     * @param string $table
     * @param array  $colsel
     * @param array  $multis
     * @param string $ts
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
