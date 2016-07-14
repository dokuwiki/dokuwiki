<?php

namespace dokuwiki\plugin\struct\meta;

use dokuwiki\plugin\struct\types\Page;

class Search {
    /**
     * This separator will be used to concat multi values to flatten them in the result set
     */
    const CONCAT_SEPARATOR = "\n!_-_-_-_-_!\n";

    /**
     * The list of known and allowed comparators
     */
    static public $COMPARATORS = array(
        '<=', '>=', '=', '<', '>', '!=', '!~', '~'
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
     * @param string $value
     * @param string $comp @see self::COMPARATORS
     * @param string $type either 'OR' or 'AND'
     */
    public function addFilter($colname, $value, $comp, $type = 'OR') {
        /* Convert certain filters into others
         * this reduces the number of supported filters to implement in types */
        if ($comp == '*~') {
            $value = '*' . $value . '*';
            $comp = '~';
        } elseif ($comp == '<>') {
            $comp = '!=';
        }

        if(!in_array($comp, self::$COMPARATORS)) throw new StructException("Bad comperator. Use " . join(',', self::$COMPARATORS));
        if($type != 'OR' && $type != 'AND') throw new StructException('Bad filter type . Only AND or OR allowed');

        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?
        $value = str_replace('*','%',$value);
        $this->filter[] = array($col, $value, $comp, $type);
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

        $result = array();
        $cursor = -1;
        while($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            if ($this->isRowEmpty($row)) {
                continue;
            }
            $cursor++;
            if($cursor < $this->range_begin) continue;
            if($this->range_end && $cursor >= $this->range_end) continue;

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

        $from = ''; // FROM clauses (tables to select from)
        $select = ''; // SELECT clauses (columns to fetch)
        $order = ''; // SORT BY clauses
        $grouping = array(); // GROUP BY
        $opts = array(); // variables
        $where = '1 = 1'; // WHERE clauses from JOINS etc.
        $fwhere = ''; // WHERE clauses from filters

        // basic tables
        $first_table = '';
        foreach($this->schemas as $schema) {
            if($first_table) {
                // follow up tables
                $from .= "\nLEFT OUTER JOIN data_{$schema->getTable()} ON data_{$first_table}.pid = data_{$schema->getTable()}.pid";
            } else {
                // first table
                $select .= "data_{$schema->getTable()}.pid as PID, ";

                $from .= 'schema_assignments, ';
                $from .= "data_{$schema->getTable()}";

                $where .= "\nAND data_{$schema->getTable()}.pid = schema_assignments.pid";
                $where .= "\nAND schema_assignments.tbl = '{$schema->getTable()}'";
                $where .= "\nAND schema_assignments.assigned = 1";
                $where .= "\nAND GETACCESSLEVEL(data_{$schema->getTable()}.pid) > 0";
                $where .= "\nAND PAGEEXISTS(data_{$schema->getTable()}.pid) = 1";
                $first_table = $schema->getTable();

                $grouping[] =  "data_{$schema->getTable()}.pid";
            }
            $where .= "\nAND data_{$schema->getTable()}.latest = 1";
        }

        // columns to select, handling multis
        $sep = self::CONCAT_SEPARATOR;
        $n = 0;
        foreach($this->columns as $col) {
            $CN = 'C' . $n++;

            if($col->isMulti()) {
                $tn = 'M' . $col->getColref();
                $select .= "GROUP_CONCAT($tn.value, '$sep') AS $CN, ";
                $from .= "\nLEFT OUTER JOIN multi_{$col->getTable()} AS $tn";
                $from .= " ON data_{$col->getTable()}.pid = $tn.pid AND data_{$col->getTable()}.rev = $tn.rev";
                $from .= " AND $tn.colref = {$col->getColref()}\n";
            } else {
                $select .= "{$col->getColName()} AS $CN, ";
                $grouping[] = $CN;
            }
        }
        $select = rtrim($select, ', ');

        // where clauses
        foreach($this->filter as $filter) {
            list($col, $value, $comp, $type) = $filter;

            /** @var $col Column */
            if($col->isMulti()) {
                $tn = 'MN' . $col->getColref(); // FIXME this joins a second time if the column was selected before
                $from .= "\nLEFT OUTER JOIN multi_{$col->getTable()} AS $tn";
                $from .= " ON data_{$col->getTable()}.pid = $tn.pid AND data_{$col->getTable()}.rev = $tn.rev";
                $from .= " AND $tn.colref = {$col->getColref()}\n";

                $column = "$tn.value";
            } else {
                $column = $col->getColName();
            }

            list($wsql, $wopt) = $col->getType()->compare($column, $comp, $value);
            $opts = array_merge($opts, $wopt);

            if(!$fwhere) $type = ''; // no type for first filter
            $fwhere .= "\n$type $wsql";
        }

        // sorting - we always sort by the single val column
        foreach($this->sortby as $sort) {
            list($col, $asc) = $sort;
            /** @var $col Column */
            $order .= $col->getColName(false) . ' ';
            $order .= ($asc) ? 'ASC' : 'DESC';
            $order .= ', ';
        }
        $order = rtrim($order, ', ');

        $fwhere = trim($fwhere);
        if($fwhere) {
            $fwhere = "AND ($fwhere\n)";
        }

        $sql = "SELECT $select\n  FROM $from\nWHERE $where\n$fwhere\nGROUP BY " . join(', ', $grouping);
        if($order) $sql .= "\nORDER BY $order";

        return array($sql, $opts);
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
     * Find a column to be used in the search
     *
     * @param string $colname may contain an alias
     * @return bool|Column
     */
    public function findColumn($colname) {
        if(!$this->schemas) throw new StructException('noschemas');

        // handling of page column is special
        if($colname == '%pageid%') {
            $schema_list = array_keys($this->schemas);
            return new PageColumn(0, new Page(), array_shift($schema_list));
        }
        // FIXME %title% needs to be handled here, too (later)

        // resolve the alias or table name
        list($table, $colname) = explode('.', $colname, 2);
        if(!$colname) {
            $colname = $table;
            $table = '';
        }
        if($table && isset($this->aliases[$table])) {
            $table = $this->aliases[$table];
        }

        if(!$colname) throw new StructException('nocolname');

        // if table name given search only that, otherwise try all for matching column name
        if($table) {
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
            if (blank($val) || is_a($col->getType(),'dokuwiki\plugin\struct\types\Page') && $val == $rowColumns["PID"]) {
                continue;
            }
            return false;
        }
        return true;
    }

}


