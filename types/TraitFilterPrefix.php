<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilderWhere;

/**
 * Class TraitFilterPrefix
 *
 * This implements a filter function for Types that use pre- or post fixes. It makes sure
 * given values are checked against the pre/postfixed values from the database
 *
 * @package dokuwiki\plugin\struct\types
 */
trait TraitFilterPrefix {

    /**
     * Comparisons are done against the full string (including prefix/postfix)
     *
     * @param QueryBuilderWhere $add
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|string[] $value
     * @param string $op
     */
    public function filter(QueryBuilderWhere $add, $tablealias, $colname, $comp, $value, $op) {
        $add = $add->where($op); // open a subgroup
        $add->where('AND', "$tablealias.$colname != ''"); // make sure the field isn't empty
        $op = 'AND';

        /** @var QueryBuilderWhere $add Where additionional queries are added to */
        if(is_array($value)) {
            $add = $add->where($op); // sub where group
            $op = 'OR';
        }
        $QB = $add->getQB();
        foreach((array) $value as $item) {
            $column = "$tablealias.$colname";

            if($this->config['prefix']) {
                $pl = $QB->addValue($this->config['prefix']);
                $column = "$pl || $column";
            }
            if($this->config['postfix']) {
                $pl = $QB->addValue($this->config['postfix']);
                $column = "$column || $pl";
            }

            $pl = $QB->addValue($item);
            $add->where($op, "$column $comp $pl");
        }
    }

}
