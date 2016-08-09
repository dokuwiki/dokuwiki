<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class AccessTableLookup
 *
 * Load and (more importantly) save data for Lookup Schemas
 *
 * @package dokuwiki\plugin\struct\meta
 */
class AccessTableLookup extends AccessTable {

    /**
     * AccessTableLookup constructor.
     *
     * @param Schema $schema Which schema to access
     * @param int $pid the row identifier (0 for new row)
     * @param int $ts Time at which the data should be read or written, 0 for now
     */
    public function __construct(Schema $schema, $pid = 0, $ts = 0) {
        parent::__construct($schema, $pid, $ts);
        if(!$this->schema->isLookup()) {
            throw new StructException('wrong schema type. use factory methods!');
        }
    }

    /**
     * Remove the current data
     */
    public function clearData() {
        if(!$this->pid) return; // no data

        /** @noinspection SqlResolve */
        $sql = "DELETE FROM ? WHERE pid = ?";
        $this->sqlite->query($sql, 'data_'.$this->schema->getTable(), $this->pid);
        $this->sqlite->query($sql, 'multi_'.$this->schema->getTable(), $this->pid);
    }

    /**
     * Save the data to the database.
     *
     * We differentiate between single-value-column and multi-value-column by the value to the respective column-name,
     * i.e. depending on if that is a string or an array, respectively.
     *
     * @param array $data typelabel => value for single fields or typelabel => array(value, value, ...) for multi fields
     * @return bool success of saving the data to the database
     * @todo this duplicates quite a bit code from SchemaData - could we avoid that?
     */
    public function saveData($data) {
        $stable = 'data_' . $this->schema->getTable();
        $mtable = 'multi_' . $this->schema->getTable();

        $singlecols = array();
        $opt = array();

        if($this->pid) {
            $singlecols[] = 'pid';
            $opt[] = $this->pid;
        }

        $colrefs = array_flip($this->labels);
        $multiopts = array();
        foreach($data as $colname => $value) {
            if(!isset($colrefs[$colname])) {
                throw new StructException("Unknown column %s in schema.", hsc($colname));
            }

            $singlecols[] = 'col' . $colrefs[$colname];
            if(is_array($value)) {
                foreach($value as $index => $multivalue) {
                    $multiopts[] = array($colrefs[$colname], $index + 1, $multivalue,);
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
        $singlesql = "REPLACE INTO $stable (" . join(',', $singlecols) . ") VALUES (" . trim(str_repeat('?,', count($opt)), ',') . ")";
        /** @noinspection SqlResolve */
        $multisql = "REPLACE INTO $mtable (pid, colref, row, value) VALUES (?,?,?,?)";

        $this->sqlite->query('BEGIN TRANSACTION');
        $ok = true;

        // insert single values
        $ok = $ok && $this->sqlite->query($singlesql, $opt);

        // get new pid if this is a new insert
        if($ok && !$this->pid) {
            $res = $this->sqlite->query('SELECT last_insert_rowid()');
            $this->pid = $this->sqlite->res2single($res);
            $this->sqlite->res_close($res);
            if(!$this->pid) $ok = false;
        }

        // insert multi values
        if($ok) foreach($multiopts as $multiopt) {
            $multiopt = array_merge(array($this->pid,), $multiopt);
            $ok = $ok && $this->sqlite->query($multisql, $multiopt);
        }

        if(!$ok) {
            $this->sqlite->query('ROLLBACK TRANSACTION');
            return false;
        }
        $this->sqlite->query('COMMIT TRANSACTION');
        return true;
    }

    protected function getLastRevisionTimestamp() {
        $return = 0;
    }

}
