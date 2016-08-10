<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class Assignments
 *
 * Manages the assignment of schemas (table names) to pages and namespaces
 *
 * @package dokuwiki\plugin\struct\meta
 */
class Assignments {

    /** @var \helper_plugin_sqlite|null */
    protected $sqlite;

    /** @var  array All the assignments patterns */
    protected $patterns;

    /**
     * Assignments constructor.
     */
    public function __construct() {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();

        if($this->sqlite) $this->loadPatterns();
    }

    /**
     * Load existing assignment patterns
     */
    protected function loadPatterns() {
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
    public function addPattern($pattern, $table) {
        // add the pattern
        $sql = 'REPLACE INTO schema_assignments_patterns (pattern, tbl) VALUES (?,?)';
        $ok = (bool) $this->sqlite->query($sql, array($pattern, $table));

        // reload patterns
        $this->loadPatterns();
        $this->propagatePageAssignments($table);


        return $ok;
    }

    /**
     * Remove an existing assignment pattern from the pattern table
     *
     * @param string $pattern
     * @param string $table
     * @return bool
     */
    public function removePattern($pattern, $table) {
        // remove the pattern
        $sql = 'DELETE FROM schema_assignments_patterns WHERE pattern = ? AND tbl = ?';
        $ok = (bool) $this->sqlite->query($sql, array($pattern, $table));

        // reload patterns
        $this->loadPatterns();

        // fetch possibly affected pages
        $sql = 'SELECT pid FROM schema_assignments WHERE tbl = ?';
        $res = $this->sqlite->query($sql, $table);
        $pagerows = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        // reevalute the pages and unassign when needed
        foreach($pagerows as $row) {
            $tables = $this->getPageAssignments($row['pid'], true);
            if(!in_array($table, $tables)) {
                $this->deassignPageSchema($row['pid'], $table);
            }
        }

        return $ok;
    }

    /**
     * Rechecks all assignments of a given page against the current patterns
     *
     * @param string $pid
     */
    public function reevaluatePageAssignments($pid) {
        // reload patterns
        $this->loadPatterns();
        $tables = $this->getPageAssignments($pid, true);

        // fetch possibly affected tables
        $sql = 'SELECT tbl FROM schema_assignments WHERE pid = ?';
        $res = $this->sqlite->query($sql, $pid);
        $tablerows = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        // reevalute the tables and apply assignments
        foreach($tablerows as $row) {
            if(in_array($row['tbl'], $tables)) {
                $this->assignPageSchema($pid, $row['tbl']);
            } else {
                $this->deassignPageSchema($pid, $row['tbl']);
            }
        }
    }

    /**
     * Clear all patterns - deassigns all pages
     *
     * This is mostly useful for testing and not used in the interface currently
     *
     * @param bool $full fully delete all previous assignments
     * @return bool
     */
    public function clear($full=false) {
        $sql = 'DELETE FROM schema_assignments_patterns';
        $ok = (bool) $this->sqlite->query($sql);

        if($full) {
            $sql = 'DELETE FROM schema_assignments';
        } else {
            $sql = 'UPDATE schema_assignments SET assigned = 0';
        }
        $ok = $ok && (bool) $this->sqlite->query($sql);

        // reload patterns
        $this->loadPatterns();

        return $ok;
    }

    /**
     * Add page to assignments
     *
     * @param string $page
     * @param string $table
     * @return bool
     */
    public function assignPageSchema($page, $table) {
        $sql = 'REPLACE INTO schema_assignments (pid, tbl, assigned) VALUES (?, ?, 1)';
        return (bool) $this->sqlite->query($sql, array($page, $table));
    }

    /**
     * Remove page from assignments
     *
     * @param string $page
     * @param string $table
     * @return bool
     */
    public function deassignPageSchema($page, $table) {
        $sql = 'REPLACE INTO schema_assignments (pid, tbl, assigned) VALUES (?, ?, 0)';
        return (bool) $this->sqlite->query($sql, array($page, $table));
    }

    /**
     * Get the whole pattern table
     *
     * @return array
     */
    public function getAllPatterns() {
        return $this->patterns;
    }

    /**
     * Returns a list of table names assigned to the given page
     *
     * @param string $page
     * @param bool $checkpatterns Should the current patterns be re-evaluated?
     * @return \string[] tables assigned
     */
    public function getPageAssignments($page, $checkpatterns=true) {
        $tables = array();
        $page = cleanID($page);

        if($checkpatterns) {
            // evaluate patterns
            $pns = ':' . getNS($page) . ':';
            foreach($this->patterns as $row) {
                if($this->matchPagePattern($row['pattern'], $page, $pns)) {
                    $tables[] = $row['tbl'];
                }
            }
        } else {
            // just select
            $sql = 'SELECT tbl FROM schema_assignments WHERE pid = ? AND assigned = 1';
            $res = $this->sqlite->query($sql, array($page));
            $list = $this->sqlite->res2arr($res);
            $this->sqlite->res_close($res);
            foreach($list as $row) {
                $tables[] = $row['tbl'];
            }
        }

        return array_unique($tables);
    }

    /**
     * Get the pages known to struct and their assignment state
     *
     * @param null|string $schema limit results to the given schema
     * @param bool $assignedonly limit results to currently assigned only
     * @return array
     */
    public function getPages($schema = null, $assignedonly = false) {
        $sql = 'SELECT pid, tbl, assigned FROM schema_assignments WHERE 1=1';

        $opts = array();
        if($schema) {
            $sql .= ' AND tbl = ?';
            $opts[] = $schema;
        }
        if($assignedonly) {
            $sql .= ' AND assigned = 1';
        }

        $sql .= ' ORDER BY pid, tbl';

        $res = $this->sqlite->query($sql, $opts);
        $list = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        $result = array();
        foreach($list as $row) {
            $pid = $row['pid'];
            $tbl = $row['tbl'];
            if(!isset($result[$pid])) $result[$pid] = array();
            $result[$pid][$tbl] = (bool) $row['assigned'];
        }

        return $result;
    }

    /**
     * Check if the given pattern matches the given page
     *
     * @param string $pattern the pattern to check against
     * @param string $page the cleaned pageid to check
     * @param string|null $pns optimization, the colon wrapped namespace of the page, set null for automatic
     * @return bool
     */
    protected function matchPagePattern($pattern, $page, $pns = null) {
        if(trim($pattern,':') == '**') return true; // match all

        // regex patterns
        if($pattern{0} == '/') {
            return (bool) preg_match($pattern, ":$page");
        }

        if(is_null($pns)) {
            $pns = ':' . getNS($page) . ':';
        }

        $ans = ':' . cleanID($pattern) . ':';
        if(substr($pattern, -2) == '**') {
            // upper namespaces match
            if(strpos($pns, $ans) === 0) {
                return true;
            }
        } else if(substr($pattern, -1) == '*') {
            // namespaces match exact
            if($ans == $pns) {
                return true;
            }
        } else {
            // exact match
            if(cleanID($pattern) == $page) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all tables of schemas that existed and stored data for the page back then
     *
     * @deprecated because we're always only interested in the current state of affairs, even when restoring.
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
        foreach($tables as $row) {
            $table = $row['tbl'];
            /** @noinspection SqlResolve */
            $sql = "SELECT pid FROM data_$table WHERE pid = ? AND rev <= ? LIMIT 1";
            $res = $this->sqlite->query($sql, $page, $ts);
            $found = $this->sqlite->res2arr($res);
            $this->sqlite->res_close($res);

            if($found) $assigned[] = $table;
        }

        return $assigned;
    }

    /**
     * fetch all pages where the schema isn't assigned, yet and reevaluate the page assignments for those pages and assign when needed
     *
     * @param $table
     */
    public function propagatePageAssignments($table) {
        $sql = 'SELECT pid FROM schema_assignments WHERE tbl != ? OR assigned != 1';
        $res = $this->sqlite->query($sql, $table);
        $pagerows = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        foreach ($pagerows as $row) {
            $tables = $this->getPageAssignments($row['pid'], true);
            if (in_array($table, $tables)) {
                $this->assignPageSchema($row['pid'], $table);
            }
        }
    }
}
