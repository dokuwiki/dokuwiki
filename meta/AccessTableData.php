<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class AccessTableData
 * @package dokuwiki\plugin\struct\meta
 *
 * This class is for accessing the data stored for a page in a schema
 *
 */
class AccessTableData extends AccessTable {

    /**
     * AccessTableData constructor
     *
     * @param Schema $schema Which schema to access
     * @param string $pid The page of which the data is for
     * @param int $ts Time at which the data should be read or written, 0 for now
     */
    public function __construct(Schema $schema, $pid, $ts = 0) {
        parent::__construct($schema, $pid, $ts);
        if($this->schema->isLookup()) {
            throw new StructException('wrong schema type. use factory methods!');
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

        foreach($this->schema->getColumns() as $col) {
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
        $stable = 'data_' . $this->schema->getTable();
        $mtable = 'multi_' . $this->schema->getTable();

        if($this->ts == 0) throw new StructException("Saving with zero timestamp does not work.");

        $colrefs = array_flip($this->labels);
        $now = $this->ts;
        $opt = array($this->pid, $now, 1);
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
        $ok = $this->sqlite->query( "UPDATE $stable SET latest = 0 WHERE latest = 1 AND pid = ?",array($this->pid));

        // insert single values
        $ok = $ok && $this->sqlite->query($singlesql, $opt);


        // insert multi values
        foreach ($multiopts as $multiopt) {
            $multiopt = array_merge(array($now, $this->pid,), $multiopt);
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
     * @return int
     */
    protected function getLastRevisionTimestamp() {
        $table = 'data_' . $this->schema->getTable();
        $where = "WHERE pid = ?";
        $opts = array($this->pid);
        if($this->ts) {
            $where .= " AND rev <= ?";
            $opts[] = $this->ts;
        }

        /** @noinspection SqlResolve */
        $sql = "SELECT rev FROM $table $where ORDER BY rev DESC LIMIT 1";
        $res = $this->sqlite->query($sql, $opts);
        $ret = (int) $this->sqlite->res2single($res);
        $this->sqlite->res_close($res);
        return $ret;
    }

}
