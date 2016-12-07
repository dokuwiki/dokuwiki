<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class QueryBuilder
 * @package dokuwiki\plugin\struct\meta
 */
class QueryBuilder {

    /** @var array placeholder -> values */
    protected $values = array();
    /** @var array (alias -> statement */
    protected $select = array();
    /** @var array (alias -> statement) */
    protected $from = array();
    /** @var array (alias -> "table"|"join") keeps how tables were added, as table or join */
    protected $type = array();
    /** @var QueryBuilderWhere */
    protected $where;
    /** @var  string[] */
    protected $orderby = array();
    /** @var  string[] */
    protected $groupby = array();

    /**
     * QueryBuilder constructor.
     */
    public function __construct() {
        $this->where = new QueryBuilderWhere();
    }

    /**
     * Adds a column to select
     *
     * If the alias already exists, the current statement for that alias will be overwritten.
     *
     * @param string $tablealias The table to select from
     * @param string $column The column to select
     * @param string $alias Under whichname to slect the column. blank for column name
     */
    public function addSelectColumn($tablealias, $column, $alias = '') {
        if($alias === '') $alias = $column;
        if(!isset($this->from[$tablealias])) {
            throw new StructException('Table Alias does not exist');
        }
        $this->select[$alias] = "$tablealias.$column";
    }

    /**
     * Add a new column selection statement
     *
     * Basically the same as @see addSelectColumn() but accepts any statement. This is useful to
     * select things like fixed strings or more complex function calls, but the correctness will not
     * be checked.
     *
     * If the alias already exists, the current statement for that alias will be overwritten.
     *
     * @param string $statement
     * @param string $alias
     */
    public function addSelectStatement($statement, $alias) {
        $this->select[$alias] = $statement;
    }

    /**
     * Return an already defined column selection statement based on the alias
     *
     * @param string $alias
     * @return string
     * @throws StructException when the alias does not exist
     */
    public function getSelectStatement($alias) {
        if(!isset($this->select[$alias])) {
            throw new StructException('No such select alias');
        }

        return $this->select[$alias];
    }

    /**
     * Adds the the table to the FROM statement part
     *
     * @param string $table the table to add
     * @param string $alias alias for the table, blank for table name
     */
    public function addTable($table, $alias = '') {
        if($alias === '') $alias = $table;
        if(isset($this->from[$alias])) {
            throw new StructException('Table Alias exists');
        }
        $this->from[$alias] = "$table AS $alias";
        $this->type[$alias] = 'table';
    }

    /**
     * Adds a LEFT JOIN clause to the FROM statement part, sorted at the correct spot
     *
     * @param string $leftalias the alias of the left table you're joining on, has to exist already
     * @param string $righttable the right table to be joined
     * @param string $rightalias an alias for the right table, blank for table name
     * @param string $onclause the ON clause condition the join is based on
     */
    public function addLeftJoin($leftalias, $righttable, $rightalias, $onclause) {
        if($rightalias === '') $rightalias = $righttable;
        if(!isset($this->from[$leftalias])) {
            throw new StructException('Table Alias does not exist');
        }
        if(isset($this->from[$rightalias])) {
            throw new StructException('Table Alias already exists');
        }

        $pos = array_search($leftalias, array_keys($this->from));
        $statement = "LEFT OUTER JOIN $righttable AS $rightalias ON $onclause";
        $this->from = $this->array_insert($this->from, array($rightalias => $statement), $pos + 1);
        $this->type[$rightalias] = 'join';
    }

    /**
     * Returns the current WHERE filters and allows to set new ones
     *
     * @return QueryBuilderWhere
     */
    public function filters() {
        return $this->where;
    }

    /**
     * Add an ORDER BY clause
     *
     * @param string $sort a single sorting condition
     */
    public function addOrderBy($sort) {
        $this->orderby[] = $sort;
    }

    /**
     * Add an GROUP BY clause
     *
     * @param string $tablealias
     * @param string $column
     */
    public function addGroupByColumn($tablealias, $column) {
        if(!isset($this->from[$tablealias])) {
            throw new StructException('Table Alias does not exist');
        }
        $this->groupby[] = "$tablealias.$column";
    }

    /**
     * Add an GROUP BY clause
     *
     * Like @see addGroupByColumn but accepts an arbitrary statement
     *
     * @param string $statement a single grouping clause
     */
    public function addGroupByStatement($statement) {
        $this->groupby[] = $statement;
    }

    /**
     * Adds a value to the statement
     *
     * This function returns the name of the placeholder you have to use in your statement, so whenever
     * you need to use a user value in a statement, call this first, then add the statement through the
     * other functions using the returned placeholder.
     *
     * @param mixed $value
     * @return string
     */
    public function addValue($value) {
        static $count = 0;
        $count++;

        $placeholder = ":!!val$count!!:"; // sqlite plugin does not support named parameters, yet so we have simulate it
        $this->values[$placeholder] = $value;
        return $placeholder;
    }

    /**
     * Creates a new table alias that has not been used before
     *
     * @param string $prefix the prefix for the alias, helps with readability of the SQL
     * @return string
     */
    public function generateTableAlias($prefix = 'T') {
        static $count = 0;
        $count++;
        return $prefix . $count;
    }

    /**
     * Returns the complete SQL statement and the values to apply
     *
     * @return array ($sql, $vals)
     */
    public function getSQL() {
        // FROM needs commas only for tables, not joins
        $from = '';
        foreach($this->from as $alias => $statement) {
            if($this->type[$alias] == 'table' && $from) {
                $from .= ",\n";
            } else {
                $from .= "\n";
            }

            $from .= $statement;
        }

        // prepare aliases for the select columns
        $selects = array();
        foreach($this->select as $alias => $select) {
            $selects[] = "$select AS $alias";
        }

        $sql =
            ' SELECT ' . join(",\n", $selects) . "\n" .
            '   FROM ' . $from . "\n" .
            '  WHERE ' . $this->where->toSQL() . "\n";

        if($this->groupby) {
            $sql .=
                'GROUP BY ' . join(",\n", $this->groupby) . "\n";
        }

        if($this->orderby) {
            $sql .=
                'ORDER BY ' . join(",\n", $this->orderby) . "\n";
        }

        return $this->fixPlaceholders($sql);
    }

    /**
     * Replaces the named placeholders with ? placeholders
     *
     * Until the sqlite plugin can use named placeholder properly
     *
     * @param string $sql
     * @return array
     */
    protected function fixPlaceholders($sql) {
        $vals = array();

        while(preg_match('/(:!!val\d+!!:)/', $sql, $m)) {
            $pl = $m[1];

            if(!array_key_exists($pl, $this->values)) {
                throw new StructException('Placeholder not found');
            }

            $sql = preg_replace("/$pl/", '?', $sql, 1);
            $vals[] = $this->values[$pl];
        }

        return array($sql, $vals);
    }

    /**
     * Insert an array into another array at a given position in an associative array
     *
     * @param array $array The initial array
     * @param array $pairs The array to insert
     * @param string $key_pos The position at which to insert
     * @link https://gist.github.com/scribu/588429 simplified
     * @return array
     */
    protected function array_insert($array, $pairs, $key_pos) {
        $result = array_slice($array, 0, $key_pos);
        $result = array_merge($result, $pairs);
        $result = array_merge($result, array_slice($array, $key_pos));
        return $result;
    }
}

