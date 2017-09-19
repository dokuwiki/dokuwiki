<?php

namespace dokuwiki\plugin\struct\meta;
use dokuwiki\plugin\struct\types\Page;

class CSVPageImporter extends CSVImporter {
    /**
     * Chceck if schema is page schema
     *
     * @throws StructException
     * @param string $table
     * @param string $file
     */
    public function __construct($table, $file) {
        parent::__construct($table, $file);

        if($this->schema->isLookup()) throw new StructException($table.' is not a page schema');
    }

    /**
     * Import page schema only when the pid header is present.
     */
    protected function readHeaders() {

        //add pid to struct
        $pageType = new Page(null, 'pid');
        $this->columns[] = new Column(0, $pageType);

        parent::readHeaders();

        if(!in_array('pid', $this->header)) throw new StructException('There is no "pid" header in the CSV. Schema not imported.');
    }

    /**
     * Creates the insert string for the single value table
     *
     * @return string
     */
    protected function getSQLforAllValues() {
        $colnames = array();
        foreach($this->columns as $i => $col) {
            $colnames[] = 'col' . $col->getColref();
        }
        //replace first column with pid
        $colnames[0] = 'pid';
        //insert rev at the end
        $colnames[] = 'rev';

        $placeholds = join(', ', array_fill(0, count($colnames), '?'));
        $colnames = join(', ', $colnames);
        $table = $this->schema->getTable();

        //replace previous data
        return "REPLACE INTO data_$table ($colnames, latest) VALUES ($placeholds, 1)";
    }

    /**
     * Add the revision.
     *
     * @param string[] $values
     * @param          $line
     * @param string   $single
     * @param string   $multi
     */
    protected function saveLine($values, $line, $single, $multi) {
        //read the lastest revision of inserted page
        $values[] = @filemtime(wikiFN($values[0]));
        parent::saveLine($values, $line, $single, $multi);
    }

    /**
     * In the paga schemas primary key is a touple of (pid, rev)
     *
     * @param string[] $values
     * @param string   $single
     * @return array(pid, rev)
     */
    protected function insertIntoSingle($values, $single) {
        parent::insertIntoSingle($values, $single);
        $pid = $values[0];
        $rev = $values[count($values) - 1];
        //primary key is touple of (pid, rev)
        return array($pid, $rev);
    }

    /**
     * Add pid and rev to insert query parameters
     *
     * @param string $multi
     * @param string $pk
     * @param string $column
     * @param string $row
     * @param string $value
     */
    protected function insertIntoMulti($multi, $pk, $column, $row, $value) {
        list($pid, $rev) = $pk;
        $this->sqlite->query($multi, array($pid, $rev, $column->getColref(), $row + 1, $value));
    }

    /**
     * In page schemas we use REPLACE instead of INSERT to prevent ambiguity
     *
     * @return string
     */
    protected function getSQLforMultiValue() {
        $table = $this->schema->getTable();
        /** @noinspection SqlResolve */
        return "REPLACE INTO multi_$table (pid, rev, colref, row, value, latest) VALUES (?,?,?,?,?,1)";
    }

    /**
     * Check if page id realy exists
     *
     * @param Column $col
     * @param mixed  $rawvalue
     * @return bool
     */
    protected function validateValue(Column $col, &$rawvalue) {
        //check if page id exists
        if($col->getLabel() == 'pid') {
            $rawvalue = cleanID($rawvalue);
            if(page_exists($rawvalue)) {
                return true;
            }
            $this->errors[] = 'Page "'.$rawvalue.'" does not exists. Skipping the row.';
            return false;
        }

        return parent::validateValue($col, $rawvalue);
    }
}
