<?php

namespace plugin\struct\meta;

use plugin\struct\types\Text;

class Search {
    /**
     * This separator will be used to concat multi values to flatten them in the result set
     */
    const CONCAT_SEPARATOR = "\n!_-_-_-_-_!\n";

    /**
     * The list of known and allowed comparators
     */
    static public $COMPARATORS = array(
        '<', '>', '<=', '>=', '!=', '!~', '~'
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

        $this->sortby[] = array($col, $asc);
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
        if(!in_array($comp, self::$COMPARATORS)) throw new StructException("Bad comperator. Use " . join(',', self::$COMPARATORS));
        if($type != 'OR' && $type != 'AND') throw new StructException('Bad filter type . Only AND or OR allowed');

        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?

        $this->filter[] = array($col, $value, $comp, $type);
    }

    /**
     * Execute this search and return the result
     *
     * The result is a two dimensional array of array. Each cell contains an array with
     * the keys 'col' (containing a Column object) and 'val' containing the value(s)
     */
    public function execute() {
        list($sql, $opts) = $this->getSQL();

        $res = $this->sqlite->query($sql, $opts);
        $data = $this->sqlite->res2arr($res);
        $this->sqlite->res_close($res);

        $result = array();
        foreach($data as $row) {
            $C = 0;
            $resrow = array();
            foreach($this->columns as $col) {
                $rescol = array();
                $rescol['col'] = $col;
                $rescol['val'] = $row["C$C"];
                if($col->isMulti()) {
                    $rescol['val'] = explode(self::CONCAT_SEPARATOR,$rescol['val']);
                }
                $resrow[] = $rescol;
                $C++;
            }
            $result[] = $resrow;
        }
        return $result;
    }

    /**
     * Transform the set search parameters into a statement
     *
     * @return array ($sql, $opts) The SQL and parameters to execute
     */
    public function getSQL() {
        if(!$this->columns) throw new StructException('nocolname');

        $from = '';
        $select = '';
        $order = '';
        $grouping = array();
        $opts = array();
        $where = '1 = 1';

        // basic tables
        $first = '';
        foreach($this->schemas as $schema) {
            if($first) {
                // follow up tables
                $from .= "\nLEFT OUTER JOIN data_{$schema->getTable()} ON data_$first.pid = data_{$schema->getTable()}.pid";
            } else {
                // first table
                $select .= "data_{$schema->getTable()}.pid as PID, ";
                $from .= "data_{$schema->getTable()}";
                $first = $schema->getTable();
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
                $select .= "data_{$col->getColName()} AS $CN, ";
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

            $where .= "\n$type $wsql";
        }

        // sorting
        foreach($this->sortby as $sort) {
            list($col, $asc) = $sort;

            /** @var $col Column */
            if($col->isMulti()) {
                // FIXME how to sort by multival?
                // FIXME what if sort by non merged multival?
            } else {
                $order .= $col->getColName().' ';
                $order .= ($asc) ? 'ASC' : 'DESC';
                $order .= ', ';
            }
        }
        $order = rtrim($order, ', ');

        $sql = "SELECT $select\n  FROM $from\nWHERE $where\nGROUP BY " . join(', ', $grouping);
        if($order) $sql .= "\nORDER BY $order";

        return array($sql, $opts);
    }

    /**
     * Find a column to be used in the search
     *
     * @param string $colname may contain an alias
     * @return bool|Column
     */
    protected function findColumn($colname) {
        if(!$this->schemas) throw new StructException('noschemas');

        // handling of page column is special
        if($colname == '%pid%') {
            return new PageColumn(0, new Text(), array_shift(array_keys($this->schemas))); //FIXME the type should be Page
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

        // if table name given search only that, otherwiese try all for matching column name
        if($table) {
            $schemas = array($table => $this->schemas[$table]);
        } else {
            $schemas = $this->schemas;
        }

        // find it
        $col = false;
        foreach($schemas as $schema) {
            $col = $schema->findColumn($colname);
            if($col) break;
        }

        return $col;
    }

}


