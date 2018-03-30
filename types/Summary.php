<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\QueryBuilderWhere;

class Summary extends AbstractBaseType {

    /**
     * When handling `%lastsummary%` get the data from the `titles` table instead the `data_` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $alias
     */
    public function select(QueryBuilder $QB, $tablealias, $colname, $alias) {
        $rightalias = $QB->generateTableAlias();
        $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
        $QB->addSelectStatement("$rightalias.lastsummary", $alias);
    }

    /**
     * When sorting `%lastsummary%`, then sort the data from the `titles` table instead the `data_` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $order
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order) {
        $rightalias = $QB->generateTableAlias();
        $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
        $QB->addOrderBy("$rightalias.lastsummary $order");
    }

    /**
     * When using `%lastsummary%`, we need to compare against the `title` table.
     *
     * @param QueryBuilderWhere $add
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilderWhere $add, $tablealias, $colname, $comp, $value, $op) {
        $QB = $add->getQB();
        $rightalias = $QB->generateTableAlias();
        $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");

        // compare against page and title
        $sub = $add->where($op);
        $pl = $QB->addValue($value);
        $sub->whereOr("$rightalias.lastsummary $comp $pl");
        return;
    }

}
