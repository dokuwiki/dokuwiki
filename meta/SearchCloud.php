<?php

namespace dokuwiki\plugin\struct\meta;


/**
 * Class SearchCloud
 *
 * The same as @see SearchConfig, but executed a search that is not pid-focused
 *
 * @package dokuwiki\plugin\struct\meta
 */
class SearchCloud extends SearchConfig {


    /**
     * Transform the set search parameters into a statement
     *
     * @return array ($sql, $opts) The SQL and parameters to execute
     */
    public function getSQL() {
        if(!$this->columns) throw new StructException('nocolname');

        $QB = new QueryBuilder();
        reset($this->schemas);
        $schema = current($this->schemas);
        $datatable = 'data_' . $schema->getTable();
        if(!$schema->isLookup()) {
            $QB->addTable('schema_assignments');
            $QB->filters()->whereAnd("$datatable.pid = schema_assignments.pid");
            $QB->filters()->whereAnd("schema_assignments.tbl = '{$schema->getTable()}'");
            $QB->filters()->whereAnd("schema_assignments.assigned = 1");
            $QB->filters()->whereAnd("GETACCESSLEVEL($datatable.pid) > 0");
            $QB->filters()->whereAnd("PAGEEXISTS($datatable.pid) = 1");
        }
        $QB->addTable($datatable);
        $QB->filters()->whereAnd("$datatable.latest = 1");

        $col = $this->columns[0];
        if($col->isMulti()) {
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

            $col->getType()->select($QB, $MN, 'value', 'tag');
            $colname = $MN . '.value';
        } else {
            $col->getType()->select($QB, $datatable, $col->getColName(), 'tag');
            $colname = $datatable . '.' . $col->getColName();
        }
        $QB->addSelectStatement("COUNT($colname)", 'count');
        $QB->addGroupByStatement('tag');
        $QB->addOrderBy('count DESC');

        return $QB->getSQL();
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
            if (!empty($this->config['min']) && $this->config['min'] > $row['count']) {
                break;
            }
            $cursor++;
            if($cursor < $this->range_begin) continue;
            if($this->range_end && $cursor >= $this->range_end) continue;

            $row['tag'] = new Value($this->columns[0], $row['tag']);
            $result[] = $row;
        }

        $this->sqlite->res_close($res);
        $this->count = $cursor + 1;
        return $result;
    }
}
