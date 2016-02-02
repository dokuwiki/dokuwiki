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

    }

    /**
     * returns the data saved for the page
     *
     * @todo we probably need the column names, too
     * @todo we have to decide how to store multi values
     */
    public function getData() {
        $table = 'data_' . $this->table;

        // prepare column names
        $columns = array();
        foreach ($this->columns as $col ){
            if(!$col->isEnabled()) continue;
            $columns[] = 'col'.$col->getColref();
        }
        $colsel = join(',', $columns);

        // figure out when this page data was saved
        if($this->ts) {
            /** @noinspection SqlResolve */
            $sql = "SELECT $colsel FROM $table WHERE pid = ? AND rev <= ? ORDER BY rev DESC LIMIT 1";
            $opt = array($this->page, $this->ts);
        } else {
            /** @noinspection SqlResolve */
            $sql = "SELECT $colsel FROM $table WHERE pid = ? ORDER BY rev DESC LIMIT 1";
            $opt = array($this->page);
        }
        $res = $this->sqlite->query($sql, $opt);
        $data = $this->sqlite->res2arr($res);

        return $data;
    }

}
