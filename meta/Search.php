<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\DateTime;
use dokuwiki\plugin\struct\types\Page;

class Search {
    /**
     * This separator will be used to concat multi values to flatten them in the result set
     */
    const CONCAT_SEPARATOR = "\n!_-_-_-_-_!\n";

    /**
     * The list of known and allowed comparators
     * (order matters)
     */
    static public $COMPARATORS = array(
        '<=', '>=', '=*', '=', '<', '>', '!=', '!~', '~',
    );

    /** @var  \helper_plugin_sqlite */
    protected $sqlite;

    /** @var Schema[] list of schemas to query */
    protected $schemas = array();

    /** @var Column[] list of columns to select */
    protected $columns = array();

    /** @var array the sorting of the result */
    protected $sortby = array();

    /** @var array the filters */
    protected $filter = array();

    /** @var array list of aliases tables can be referenced by */
    protected $aliases = array();

    /** @var  int begin results from here */
    protected $range_begin = 0;

    /** @var  int end results here */
    protected $range_end = 0;

    /** @var int the number of results */
    protected $count = -1;
    /** @var  string[] the PIDs of the result rows */
    protected $result_pids = null;

    /**
     * Search constructor.
     */
    public function __construct() {
        /** @var \helper_plugin_struct_db $plugin */
        $plugin = plugin_load('helper', 'struct_db');
        $this->sqlite = $plugin->getDB();
    }

    /**
     * Add a schema to be searched
     *
     * Call multiple times for multiple schemas.
     *
     * @param string $table
     * @param string $alias
     */
    public function addSchema($table, $alias = '') {
        $this->schemas[$table] = new Schema($table);
        if($alias) $this->aliases[$alias] = $table;
    }

    /**
     * Add a column to be returned by the search
     *
     * Call multiple times for multiple columns. Be sure the referenced tables have been
     * added before
     *
     * @param string $colname may contain an alias
     */
    public function addColumn($colname) {
        if($this->processWildcard($colname)) return; // wildcard?
        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?
        $this->columns[] = $col;
    }

    /**
     * Add sorting options
     *
     * Call multiple times for multiple columns. Be sure the referenced tables have been
     * added before
     *
     * @param string $colname may contain an alias
     * @param bool $asc sort direction (ASC = true, DESC = false)
     */
    public function addSort($colname, $asc = true) {
        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?

        $this->sortby[$col->getFullQualifiedLabel()] = array($col, $asc);
    }

    /**
     * Returns all set sort columns
     *
     * @return array
     */
    public function getSorts() {
        return $this->sortby;
    }

    /**
     * Adds a filter
     *
     * @param string $colname may contain an alias
     * @param string|string[] $value
     * @param string $comp @see self::COMPARATORS
     * @param string $op either 'OR' or 'AND'
     */
    public function addFilter($colname, $value, $comp, $op = 'OR') {
        /* Convert certain filters into others
         * this reduces the number of supported filters to implement in types */
        if($comp == '*~') {
            $value = $this->filterWrapAsterisks($value);
            $comp = '~';
        } elseif($comp == '<>') {
            $comp = '!=';
        }

        if(!in_array($comp, self::$COMPARATORS)) throw new StructException("Bad comperator. Use " . join(',', self::$COMPARATORS));
        if($op != 'OR' && $op != 'AND') throw new StructException('Bad filter type . Only AND or OR allowed');

        $col = $this->findColumn($colname);
        if(!$col) return; // ignore missing columns, filter might have been for different schema

        // map filter operators to SQL syntax
        switch($comp) {
            case '~':
                $comp = 'LIKE';
                break;
            case '!~':
                $comp = 'NOT LIKE';
                break;
            case '=*':
                $comp = 'REGEXP';
                break;
        }

        // we use asterisks, but SQL wants percents
        if($comp == 'LIKE' || $comp == 'NOT LIKE') {
            $value = $this->filterChangeToLike($value);
        }

        // add the filter
        $this->filter[] = array($col, $value, $comp, $op);
    }

    /**
     * Wrap given value in asterisks
     *
     * @param string|string[] $value
     * @return string|string[]
     */
    protected function filterWrapAsterisks($value) {
        $map = function ($input) {
            return "*$input*";
        };

        if(is_array($value)) {
            $value = array_map($map, $value);
        } else {
            $value = $map($value);
        }
        return $value;
    }

    /**
     * Change given string to use % instead of *
     *
     * @param string|string[] $value
     * @return string|string[]
     */
    protected function filterChangeToLike($value) {
        $map = function ($input) {
            return str_replace('*', '%', $input);
        };

        if(is_array($value)) {
            $value = array_map($map, $value);
        } else {
            $value = $map($value);
        }
        return $value;
    }

    /**
     * Set offset for the results
     *
     * @param int $offset
     */
    public function setOffset($offset) {
        $limit = 0;
        if($this->range_end) {
            // if there was a limit set previously, the range_end needs to be recalculated
            $limit = $this->range_end - $this->range_begin;
        }
        $this->range_begin = $offset;
        if($limit) $this->setLimit($limit);
    }

    /**
     * Limit results to this number
     *
     * @param int $limit Set to 0 to disable limit again
     */
    public function setLimit($limit) {
        if($limit) {
            $this->range_end = $this->range_begin + $limit;
        } else {
            $this->range_end = 0;
        }
    }

    /**
     * Return the number of results (regardless of limit and offset settings)
     *
     * Use this to implement paging. Important: this may only be called after running @see execute()
     *
     * @return int
     */
    public function getCount() {
        if($this->count < 0) throw new StructException('Count is only accessible after executing the search');
        return $this->count;
    }

    /**
     * Returns the PID associated with each result row
     *
     * Important: this may only be called after running @see execute()
     *
     * @return \string[]
     */
    public function getPids() {
        if($this->result_pids === null) throw new StructException('PIDs are only accessible after executing the search');
        return $this->result_pids;
    }

    /**
     * Execute this search and return the result
     *
     * The result is a two dimensional array of Value()s.
     *
     * This will always query for the full result (not using offset and limit) and then
     * return the wanted range, setting the count (@see getCount) to the whole result number
     *
     * @return Value[][]
     */
    public function execute() {
        list($sql, $opts) = $this->getSQL();

        /** @var \PDOStatement $res */
        $res = $this->sqlite->query($sql, $opts);
        if($res === false) throw new StructException("SQL execution failed for\n\n$sql");

        $this->result_pids = array();
        $result = array();
        $cursor = -1;
        while($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            if($this->isRowEmpty($row)) {
                continue;
            }
            $cursor++;
            if($cursor < $this->range_begin) continue;
            if($this->range_end && $cursor >= $this->range_end) continue;

            $this->result_pids[] = $row['PID'];

            $C = 0;
            $resrow = array();
            foreach($this->columns as $col) {
                $val = $row["C$C"];
                if($col->isMulti()) {
                    $val = explode(self::CONCAT_SEPARATOR, $val);
                }
                $resrow[] = new Value($col, $val);
                $C++;
            }
            $result[] = $resrow;
        }

        $this->sqlite->res_close($res);
        $this->count = $cursor + 1;
        return $result;
    }

    /**
     * Transform the set search parameters into a statement
     *
     * @return array ($sql, $opts) The SQL and parameters to execute
     */
    public function getSQL() {
        if(!$this->columns) throw new StructException('nocolname');

        $QB = new QueryBuilder();

        // basic tables
        $first_table = '';
        foreach($this->schemas as $schema) {
            $datatable = 'data_' . $schema->getTable();
            if($first_table) {
                // follow up tables
                $QB->addLeftJoin($first_table, $datatable, $datatable, "$first_table.pid = $datatable.pid");
            } else {
                // first table
                $QB->addTable('schema_assignments');
                $QB->addTable($datatable);
                $QB->addSelectColumn($datatable, 'pid', 'PID');
                $QB->addGroupByColumn($datatable, 'pid');

                $QB->filters()->whereAnd("$datatable.pid = schema_assignments.pid");
                $QB->filters()->whereAnd("schema_assignments.tbl = '{$schema->getTable()}'");
                $QB->filters()->whereAnd("schema_assignments.assigned = 1");
                $QB->filters()->whereAnd("GETACCESSLEVEL($datatable.pid) > 0");
                $QB->filters()->whereAnd("PAGEEXISTS($datatable.pid) = 1");

                $first_table = $datatable;
            }
            $QB->filters()->whereAnd("$datatable.latest = 1");
        }

        // columns to select, handling multis
        $sep = self::CONCAT_SEPARATOR;
        $n = 0;
        foreach($this->columns as $col) {
            $CN = 'C' . $n++;

            if($col->isMulti()) {
                $datatable = "data_{$col->getTable()}";
                $multitable = "multi_{$col->getTable()}";
                $MN = 'M' . $col->getColref();

                $QB->addLeftJoin(
                    $datatable,
                    $multitable,
                    $MN,
                    "$datatable.pid = $MN.pid AND
                     $datatable.rev = $MN.rev AND
                     $MN.colref = {$col->getColref()}"
                );

                $col->getType()->select($QB, $MN, 'value', $CN);
                $sel = $QB->getSelectStatement($CN);
                $QB->addSelectStatement("GROUP_CONCAT($sel, '$sep')", $CN);
            } else {
                $col->getType()->select($QB, 'data_' . $col->getTable(), $col->getColName(), $CN);
                $QB->addGroupByStatement($CN);
            }
        }

        // where clauses
        foreach($this->filter as $filter) {
            list($col, $value, $comp, $op) = $filter;

            $datatable = "data_{$col->getTable()}";
            $multitable = "multi_{$col->getTable()}";

            /** @var $col Column */
            if($col->isMulti()) {
                $MN = 'MN' . $col->getColref(); // FIXME this joins a second time if the column was selected before

                $QB->addLeftJoin(
                    $datatable,
                    $multitable,
                    $MN,
                    "$datatable.pid = $MN.pid AND
                     $datatable.rev = $MN.rev AND
                     $MN.colref = {$col->getColref()}"
                );
                $coltbl = $MN;
                $colnam = 'value';
            } else {
                $coltbl = $datatable;
                $colnam = $col->getColName();
            }

            $col->getType()->filter($QB, $coltbl, $colnam, $comp, $value, $op); // type based filter
        }

        // sorting - we always sort by the single val column
        foreach($this->sortby as $sort) {
            list($col, $asc) = $sort;
            /** @var $col Column */
            $col->getType()->sort($QB, 'data_'.$col->getTable(), $col->getColName(false), $asc ? 'ASC' : 'DESC');
        }

        return $QB->getSQL();
    }

    /**
     * Returns all the columns that where added to the search
     *
     * @return Column[]
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * Checks if the given column is a * wildcard
     *
     * If it's a wildcard all matching columns are added to the column list, otherwise
     * nothing happens
     *
     * @param string $colname
     * @return bool was wildcard?
     */
    protected function processWildcard($colname) {
        list($colname, $table) = $this->resolveColumn($colname);
        if($colname !== '*') return false;

        // no table given? assume the first is meant
        if($table === null) {
            $schema_list = array_keys($this->schemas);
            $table = $schema_list[0];
        }

        $schema = $this->schemas[$table];
        if(!$schema) return false;
        $this->columns = array_merge($this->columns, $schema->getColumns(false));
        return true;
    }

    /**
     * Split a given column name into table and column
     *
     * Handles Aliases. Table might be null if none given.
     *
     * @param $colname
     * @return array (colname, table)
     */
    protected function resolveColumn($colname) {
        if(!$this->schemas) throw new StructException('noschemas');

        // resolve the alias or table name
        list($table, $colname) = explode('.', $colname, 2);
        if(!$colname) {
            $colname = $table;
            $table = null;
        }
        if($table && isset($this->aliases[$table])) {
            $table = $this->aliases[$table];
        }

        if(!$colname) throw new StructException('nocolname');

        return array($colname, $table);
    }

    /**
     * Find a column to be used in the search
     *
     * @param string $colname may contain an alias
     * @return bool|Column
     */
    public function findColumn($colname) {
        if(!$this->schemas) throw new StructException('noschemas');

        // handling of page and title column is special - we add a "fake" column
        $schema_list = array_keys($this->schemas);
        if($colname == '%pageid%') {
            return new PageColumn(0, new Page(), $schema_list[0]);
        }
        if($colname == '%title%') {
            return new PageColumn(0, new Page(array('usetitles' => true)), $schema_list[0]);
        }
        if($colname == '%lastupdate%') {
            return new RevisionColumn(0, new DateTime(), $schema_list[0]);
        }

        list($colname, $table) = $this->resolveColumn($colname);

        // if table name given search only that, otherwise try all for matching column name
        if($table !== null) {
            $schemas = array($table => $this->schemas[$table]);
        } else {
            $schemas = $this->schemas;
        }

        // find it
        $col = false;
        foreach($schemas as $schema) {
            if(empty($schema)) {
                continue;
            }
            $col = $schema->findColumn($colname);
            if($col) break;
        }

        return $col;
    }

    /**
     * Check if a row is empty / only contains a reference to itself
     *
     * @param array $rowColumns an array as returned from the database
     * @return bool
     */
    private function isRowEmpty($rowColumns) {
        $C = 0;
        foreach($this->columns as $col) {
            $val = $rowColumns["C$C"];
            $C += 1;
            if(blank($val) || is_a($col->getType(), 'dokuwiki\plugin\struct\types\Page') && $val == $rowColumns["PID"]) {
                continue;
            }
            return false;
        }
        return true;
    }

}


