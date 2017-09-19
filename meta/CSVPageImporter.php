<?php

namespace dokuwiki\plugin\struct\meta;
use dokuwiki\plugin\struct\types\Page;

class CSVPageImporter extends CSVImporter {

    protected $importedPids = array();

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

        return "INSERT INTO data_$table ($colnames, latest) VALUES ($placeholds, 1)";
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
        //create new page revision
        $pid = $values[0];
        $helper = plugin_load('helper', 'struct');
        $revision = $helper->createPageRevision($pid, 'CSV data imported');
        p_get_metadata($pid); // reparse the metadata of the page top update the titles/rev/lasteditor table

        // make sure this schema is assigned
        /** @noinspection PhpUndefinedVariableInspection */
        Assignments::getInstance()->assignPageSchema(
            $pid,
            $this->schema->getTable()
        );

        //add page revision to values
        $values[] = $revision;

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
        $pid = $values[0];
        $rev = $values[count($values) - 1];

        //update latest
        $table = $this->schema->getTable();
        $this->sqlite->query("UPDATE data_$table SET latest = 0 WHERE latest = 1 AND pid = ?", array($pid));

        //insert into table
        parent::insertIntoSingle($values, $single);

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

        //update latest
        $table = $this->schema->getTable();
        $this->sqlite->query("UPDATE multi_$table SET latest = 0 WHERE latest = 1 AND pid = ?", array($pid));

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
        return "INSERT INTO multi_$table (pid, rev, colref, row, value, latest) VALUES (?,?,?,?,?,1)";
    }

    /**
     * Check if page id realy exists
     *
     * @param Column $col
     * @param mixed  $rawvalue
     * @return bool
     */
    protected function validateValue(Column $col, &$rawvalue) {
        //check if page id exists and schema is bounded to the page
        if($col->getLabel() == 'pid') {
            $pid = cleanID($rawvalue);
            if (isset($this->importedPids[$pid])) {
                $this->errors[] = 'Page "'.$pid.'" already imported. Skipping the row.';
                return false;
            }
            if(page_exists($pid)) {
                //check if schema is assigned to page
                $tables = Assignments::getInstance()->getPageAssignments($pid, true);
                if (!in_array($this->schema->getTable(), $tables)) {
                    $this->errors[] = 'Schema not assigned to page "'.$pid.'"';
                    return false;
                }
                $this->importedPids[$pid] = true;
                return true;
            }
            $this->errors[] = 'Page "'.$pid.'" does not exists. Skipping the row.';
            return false;
        }

        return parent::validateValue($col, $rawvalue);
    }
}
