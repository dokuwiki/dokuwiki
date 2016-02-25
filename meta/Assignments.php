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
    protected $patterns;

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
     * Load existing assignment patterns
     */
    protected function load() {
        $sql = 'SELECT * FROM schema_assignments_patterns ORDER BY pattern';
        $res = $this->sqlite->query($sql);
        $this->patterns = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);
    }

    /**
     * Add a new assignment pattern to the pattern table
     *
     * @param string $pattern
     * @param string $table
     * @return bool
     */
    public function add($pattern, $table) {
        $sql = 'REPLACE INTO schema_assignments_patterns (pattern, tbl) VALUES (?,?)';
        return (bool) $this->sqlite->query($sql, array($pattern, $table));
    }

    /**
     * Remove an existing assignment pattern from the pattern table
     *
     * @param string $pattern
     * @param string $table
     * @return bool
     */
    public function remove($pattern, $table) {
        $sql = 'DELETE FROM schema_assignments_patterns WHERE pattern = ? AND tbl = ?';
        return (bool) $this->sqlite->query($sql, array($pattern, $table));
    }

    /**
     * Get the whole pattern table
     *
     * @return array
     */
    public function getAll() {
        return $this->patterns;
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

        foreach($this->patterns as $row) {
            $pat = $row['pattern'];
            $tbl = $row['tbl'];

            $ans = ':' . cleanID($pat) . ':';

            if(substr($pat, -2) == '**') {
                // upper namespaces match
                if(strpos($pns, $ans) === 0) {
                    $tables[] = $tbl;
                }
            } else if(substr($pat, -1) == '*') {
                // namespaces match exact
                if($ans == $pns) {
                    $tables[] = $tbl;
                }
            } else {
                // exact match
                if(cleanID($pat) == $page) {
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
