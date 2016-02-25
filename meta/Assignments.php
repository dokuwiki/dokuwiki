<?php

namespace plugin\struct\meta;

/**
 * Class Assignments
 *
 * Manages the assignment of schemas (table names) to pages and namespaces
 *
 * @package plugin\struct\meta
 */
class Assignments {

    /** @var \helper_plugin_sqlite|null */
    protected $sqlite;

    /** @var  array All the assignments */
    protected $assignments;

    /**
     * Assignments constructor.
     */
    public function __construct() {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();

        if($this->sqlite) $this->load();
    }

    /**
     * Load existing assignments
     */
    protected function load() {
        $sql = 'SELECT * FROM schema_assignments ORDER BY assign';
        $res = $this->sqlite->query($sql);
        $this->assignments = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);
    }

    /**
     * Add a new assignment to the assignment table
     *
     * @param string $assign
     * @param string $table
     * @return bool
     */
    public function add($assign, $table) {
        $sql = 'REPLACE INTO schema_assignments (assign, tbl) VALUES (?,?)';
        return (bool) $this->sqlite->query($sql, array($assign, $table));
    }

    /**
     * Remove an existing assignment from the assignment table
     *
     * @param string $assign
     * @param string $table
     * @return bool
     */
    public function remove($assign, $table) {
        $sql = 'DELETE FROM schema_assignments WHERE assign = ? AND tbl = ?';
        return (bool) $this->sqlite->query($sql, array($assign, $table));
    }

    /**
     * Get the whole assignments table
     *
     * @return array
     */
    public function getAll() {
        return $this->assignments;
    }

    /**
     * Returns a list of table names assigned to the given page
     *
     * @param string $page
     * @return string[] tables assigned
     */
    public function getPageAssignments($page) {
        $tables = array();

        $page = cleanID($page);
        $pns = ':' . getNS($page) . ':';

        foreach($this->assignments as $row) {
            $ass = $row['assign'];
            $tbl = $row['tbl'];

            $ans = ':' . cleanID($ass) . ':';

            if(substr($ass, -2) == '**') {
                // upper namespaces match
                if(strpos($pns, $ans) === 0) {
                    $tables[] = $tbl;
                }
            } else if(substr($ass, -1) == '*') {
                // namespaces match exact
                if($ans == $pns) {
                    $tables[] = $tbl;
                }
            } else {
                // exact match
                if(cleanID($ass) == $page) {
                    $tables[] = $tbl;
                }
            }
        }

        return array_unique($tables);
    }

    /**
     * Returns all tables of schemas that existed and stored data for the page back then
     *
     * @todo this is not used currently and can probably be removed again, because we're
     *       always only interested in the current state of affairs, even when restoring.
     *
     * @param string $page
     * @param string $ts
     * @return array
     */
    public function getHistoricAssignments($page, $ts) {
        $sql = "SELECT DISTINCT tbl FROM schemas WHERE ts <= ? ORDER BY ts DESC";
        $res = $this->sqlite->query($sql, $ts);
        $tables = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        $assigned = array();
        foreach ($tables as $row) {
            $table = $row['tbl'];
            $sql = "SELECT pid FROM data_$table WHERE pid = ? AND rev <= ? LIMIT 1";
            $res = $this->sqlite->query($sql, $page, $ts);
            $found = $this->sqlite->res2arr($res);
            $this->sqlite->res_close($res);

            if($found) $assigned[] = $table;
        }

        return $assigned;
    }
}
