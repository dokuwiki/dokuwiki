<?php

namespace plugin\struct\meta;

use Exception;

class Search {

    /** @var Schema[] list of schemas to query */
    protected $schemas = array();

    /** @var Column[] list of columns to select */
    protected $columns = array();

    /** @var array the sorting of the result */
    protected $sortby = array();

    /** @var array the or filters */
    protected $filteror = array();

    /** @var array the and filters */
    protected $filterand = array();

    /** @var array list of aliases tables can be referenced by */
    protected $aliases = array();

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
     * Adds an ORed filter
     *
     * @param string $colname may contain an alias
     * @param string $value
     * @param string $comp
     */
    public function addFilterOr($colname, $value, $comp) {
        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?

        $this->filteror[] = array($col, $value, $comp);
    }

    /**
     * Adds an ANDed filter
     *
     * @param string $colname may contain an alias
     * @param string $value
     * @param string $comp
     */
    public function addFilterAnd($colname, $value, $comp) {
        $col = $this->findColumn($colname);
        if(!$col) return; //FIXME do we really want to ignore missing columns?

        $this->filterand[] = array($col, $value, $comp);
    }

    /**
     * Transform the set search parameters into a statement
     *
     * @todo limit to the newest data!
     * @return string
     */
    public function getSQL() {
        if(!$this->columns) throw new SearchException('nocolname');

        // basic tables
        $from = '';
        foreach($this->schemas as $schema) {
            $from .= 'data_' . $schema->getTable() . ', ';

            // fixme join the multiple tables together by pid
        }
        $from = rtrim($from, ', ');

        // columns to select, handling multis
        $select = '';
        $n = 0;
        foreach($this->columns as $col) {
            $CN = 'C'.$n++;

            if($col->isMulti()) {
                $tn = 'M' . $col->getColref();
                $select .= "$tn.value AS $CN, ";
                $from .= "\nLEFT OUTER JOIN multivals $tn";
                $from .= " ON DATA.pid = $tn.pid AND DATA.rev = $tn.rev";
                $from .= " AND $tn.tbl = '{$col->getTable()}' AND $tn.colref = {$col->getColref()}\n";
            } else {
                $select .=  'data_'.$col->getTable().'.col' . $col->getColref() . " AS $CN, ";
            }
        }
        $select = rtrim($select, ', ');


        $sql = "SELECT $select\n  FROM $from";
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
