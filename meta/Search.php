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
     * @param string $op either 'OR' or 'AND'
     */
    public function addFilter($colname, $value, $comp, $op = 'OR') {
        /* Convert certain filters into others
         * this reduces the number of supported filters to implement in types */
        if ($comp == '*~') {
            $value = '*' . $value . '*';
            $comp = '~';
        } elseif ($comp == '<>') {
            $comp = '!=';
        }

        if(!in_array($comp, self::$COMPARATORS)) throw new StructException("Bad comperator. Use " . join(',', self::$COMPARATORS));
        if($op != 'OR' && $op != 'AND') throw new StructException('Bad filter type . Only AND or OR allowed');

        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?
        $value = str_replace('*','%',$value);
        $this->filter[] = array($col, $value, $comp, $op);
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

        $QB = new QueryBuilder();

        // basic tables
        $first_table = '';
        foreach($this->schemas as $schema) {
            $datatable = 'data_'.$schema->getTable();
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

                $col->getType()->select($QB, $MN, 'value' , $CN);
                $sel = $QB->getSelectStatement($CN);
                $QB->addSelectStatement("GROUP_CONCAT($sel, '$sep')", $CN);
            } else {
                $col->getType()->select($QB, 'data_'.$col->getTable(), $col->getColName() , $CN);
                $QB->addGroupByStatement($CN);
            }
        }

        // where clauses
        foreach($this->filter as $filter) {
            list($col, $value, $comp, $op) = $filter;

            /** @var $col Column */
            if($col->isMulti()) {
                $datatable = "data_{$col->getTable()}";
                $multitable = "multi_{$col->getTable()}";
                $MN = 'MN' . $col->getColref(); // FIXME this joins a second time if the column was selected before

                $QB->addLeftJoin(
                    $datatable,
                    $multitable,
                    $MN,
                    "$datatable.pid = $MN.pid AND
                     $datatable.rev = $MN.rev AND
                     $MN.colref = {$col->getColref()}"
                );
                $column = "$MN.value";
            } else {
                $column = $col->getFullColName();
            }

            list($wsql, $wopt) = $col->getType()->compare($column, $comp, $value);

            // FIXME temporary until compare() uses the query builder directly
            foreach($wopt as $opt) {
                $key = $QB->addValue($opt);
                $wsql = preg_replace('/\?/', $key, $wsql, 1);
            }

            $QB->filters()->where($op, $wsql);
        }

        // sorting - we always sort by the single val column
        foreach($this->sortby as $sort) {
            list($col, $asc) = $sort;
            /** @var $col Column */
            $QB->addOrderBy($col->getFullColName(false) . ' '.(($asc) ? 'ASC' : 'DESC'));
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

}


