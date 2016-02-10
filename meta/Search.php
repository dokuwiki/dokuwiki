<?php

namespace plugin\struct\meta;

use Exception;

class Search {
    /**
     * This separator will be used to concat multi values to flatten them in the result set
     */
    const CONCAT_SEPARATOR = "\n!_-_-_-_-_!\n";

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
     * @param string $comp ('=', '<', '>', '<=', '>=', '~')
     * @param string $type either 'OR' or 'AND'
     */
    public function addFilter($colname, $value, $comp, $type = 'OR') {
        if(!in_array($comp, array('<', '>', '<=', '>=', '~'))) throw new SearchException("Bad comperator. Use '=', '<', '>', '<=', '>=' or '~'");
        if($type != 'OR' && $type != 'AND') throw new SearchException('Bad filter type . Only AND or OR allowed');

        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?

        $this->filter[] = array($col, $value, $comp, $type);
    }

    /**
     * Transform the set search parameters into a statement
     *
     * @todo limit to the newest data!
     * @return string
     */
    public function getSQL() {
        if(!$this->columns) throw new SearchException('nocolname');

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
                $from .= "\nLEFT OUTER JOIN multivals AS $tn";
                $from .= " ON data_{$col->getTable()}.pid = $tn.pid AND data_{$col->getTable()}.rev = $tn.rev";
                $from .= " AND $tn.tbl = '{$col->getTable()}' AND $tn.colref = {$col->getColref()}\n";
            } else {
                $select .= 'data_' . $col->getTable() . ' . col' . $col->getColref() . " AS $CN, ";
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
                $from .= "\nLEFT OUTER JOIN multivals AS $tn";
                $from .= " ON data_{$col->getTable()}.pid = $tn.pid AND data_{$col->getTable()}.rev = $tn.rev";
                $from .= " AND $tn.tbl = '{$col->getTable()}' AND $tn.colref = {$col->getColref()}\n";

                $column = "$tn.value";
            } else {
                $column = "data_{$col->getTable()}.col{$col->getColref()}";
            }

            list($wsql, $wopt) = $col->getType()->compare($column, $comp, $value);
            $opts = array_merge($opts, $wopt);

            $where .= " $type $wsql";
        }

        // sorting
        foreach($this->sortby as $sort) {
            list($col, $asc) = $sort;

            /** @var $col Column */
            if($col->isMulti()) {
                // FIXME how to sort by multival?
                // FIXME what if sort by non merged multival?
            } else {
                $order .= "data_{$col->getTable()}.col{$col->getColref()} ";
                $order .= ($asc) ? 'ASC' : 'DESC';
                $order .= ', ';
            }
        }
        $order = rtrim($order, ', ');

        $sql = "SELECT $select\n  FROM $from\nWHERE $where\nGROUP BY " . join(', ', $grouping);
        if($order) $sql .= "\nORDER BY $order";

        {#debugging
            $res = $this->sqlite->query($sql, $opts);
            $data = $this->sqlite->res2arr($res);
            $this->sqlite->res_close($res);
            print_r($data);
        }

        return $sql;
    }

    /**
     * Find a column to be used in the search
     *
     * @param string $colname may contain an alias
     * @return bool|Column
     */
    protected function findColumn($colname) {
        if(!$this->schemas) throw new SearchException('noschemas');

        // resolve the alias or table name
        list($table, $colname) = explode('.', $colname, 2);
        if(!$colname) {
            $colname = $table;
            $table = '';
        }
        if($table && isset($this->aliases[$table])) {
            $table = $this->aliases[$table];
        }

        if(!$colname) throw new SearchException('nocolname');

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

/**
 * Class SearchException
 *
 * A translatable exception
 *
 * @package plugin\struct\meta
 */
class SearchException extends \RuntimeException {
    public function __construct($message, $code = -1, Exception $previous = null) {
        /** @var \action_plugin_struct_autoloader $plugin */
        $plugin = plugin_load('action', 'struct_autoloader');
        $trans = $plugin->getLang('searchex_' . $message);
        if(!$trans) $trans = $message;
        parent::__construct($trans, $code, $previous);
    }
}
