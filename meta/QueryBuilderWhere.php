<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class QueryWhere
 * @package dokuwiki\plugin\struct\meta
 */
class QueryBuilderWhere {

    /** @var  QueryBuilderWhere[]|string */
    protected $statement;
    /** @var string */
    protected $type = 'AND';

    /**
     * Create a new WHERE clause
     *
     * @param string $type The type of the statement, either 'AND' or 'OR'
     * @param null|string $statement The statement or null if this should hold sub statments
     */
    public function __construct($type = 'AND', $statement = null) {
        $this->type = $type;
        if($statement === null) {
            $this->statement = array();
        } else {
            $this->statement = $statement;
        }
    }

    /**
     * Adds another AND clause
     *
     * @param string $statement
     * @return $this
     */
    public function whereAnd($statement) {
        return $this->where('AND', $statement);
    }

    /**
     * Adds another OR clause
     *
     * @param $statement
     * @return $this
     */
    public function whereOr($statement) {
        return $this->where('OR', $statement);
    }

    /**
     * Add a new AND sub clause on which more statements can be added
     *
     * @return QueryBuilderWhere
     */
    public function whereSubAnd() {
        return $this->where('AND', null);
    }

    /**
     * Add a new OR sub clause on which more statements can be added
     *
     * @return QueryBuilderWhere
     */
    public function whereSubOr() {
        return $this->where('OR', null);
    }

    /**
     * Adds another statement to this sub clause
     *
     * @param string $op either AND or OR
     * @param null|string $statement null creates a new sub clause
     * @return $this|QueryBuilderWhere
     * @throws StructException when this is not a sub clause
     */
    public function where($op = 'AND', $statement = null) {
        if(!is_array($this->statement)) {
            throw new StructException('This WHERE is not a sub clause and can not have additional clauses');
        }
        if($op != 'AND' && $op != 'OR') {
            throw new StructException('Bad logical operator');
        }
        $where = new QueryBuilderWhere($op, $statement);
        $this->statement[] = $where;

        if($statement) {
            return $this;
        } else {
            return $where;
        }
    }

    /**
     * @param bool $first is this the first where statement? Then the type is ignored
     * @return string
     */
    public function toSQL($first = true) {
        if(!$this->statement) return '';

        $sql = ' ';
        if(!$first) $sql .= $this->type . ' ';

        if(is_array($this->statement)) {
            $first = true;
            $sql .= '(';
            foreach($this->statement as $where) {
                $sql .= $where->toSQL($first);
                $first = false;
            }
            $sql .= ' )';
        } else {
            $sql .= $this->statement;
        }

        return $sql;
    }
}
