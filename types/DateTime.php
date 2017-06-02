<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\DateFormatConverter;
use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\QueryBuilderWhere;
use dokuwiki\plugin\struct\meta\ValidationException;

class DateTime extends Date {

    protected $config = array(
        'format' => '', // filled by constructor
        'prefilltoday' => false
    );

    /**
     * DateTime constructor.
     *
     * @param array|null $config
     * @param string $label
     * @param bool $ismulti
     * @param int $tid
     */
    public function __construct($config = null, $label = '', $ismulti = false, $tid = 0) {
        global $conf;
        $this->config['format'] = DateFormatConverter::toDate($conf['dformat']);

        parent::__construct($config, $label, $ismulti, $tid);
    }

    /**
     * Return the editor to edit a single value
     *
     * @param string $name     the form name where this has to be stored
     * @param string $rawvalue the current value
     * @param string $htmlID
     *
     * @return string html
     */
    public function valueEditor($name, $rawvalue, $htmlID) {
        $name = hsc($name);
        $rawvalue = hsc($rawvalue);

        if($this->config['prefilltoday'] && !$rawvalue) {
            $rawvalue = date('Y-m-d H:i:s');
        }

        $id = !empty($htmlID) ? "id=\"$htmlID\"" : '';

        $html = "<input class=\"struct_datetime\" name=\"$name\" value=\"$rawvalue\" $id />";
        return "$html";
    }

    /**
     * Validate a single value
     *
     * This function needs to throw a validation exception when validation fails.
     * The exception message will be prefixed by the appropriate field on output
     *
     * @param string|array $rawvalue
     * @return string
     * @throws ValidationException
     */
    public function validate($rawvalue) {
        $rawvalue = trim($rawvalue);
        list($date, $time) = explode(' ', $rawvalue, 2);
        $date = trim($date);
        $time = trim($time);

        list($year, $month, $day) = explode('-', $date, 3);
        if(!checkdate((int) $month, (int) $day, (int) $year)) {
            throw new ValidationException('invalid datetime format');
        }

        list($h, $m, $s) = explode(':', $time, 3);
        $h = (int) $h;
        $m = (int) $m;
        $s = (int) $s;
        if($h < 0 || $h > 23 || $m < 0 || $m > 59 || $s < 0 || $s > 59) {
            throw new ValidationException('invalid datetime format');
        }

        return sprintf("%d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $h, $m, $s);
    }

    /**
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $alias
     */
    public function select(QueryBuilder $QB, $tablealias, $colname, $alias) {
        $col = "$tablealias.$colname";

        // when accessing the revision column we need to convert from Unix timestamp
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\RevisionColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
            $col = "DATETIME($rightalias.lastrev, 'unixepoch', 'localtime')";
        }

        $QB->addSelectStatement($col, $alias);
    }

    /**
     * @param QueryBuilderWhere $add
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilderWhere $add, $tablealias, $colname, $comp, $value, $op) {
        $col = "$tablealias.$colname";

        // when accessing the revision column we need to convert from Unix timestamp
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\RevisionColumn')) {
            $QB = $add->getQB();
            $rightalias = $QB->generateTableAlias();
            $col = "DATETIME($rightalias.lastrev, 'unixepoch', 'localtime')";
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
        }

        /** @var QueryBuilderWhere $add Where additionional queries are added to*/
        if(is_array($value)) {
            $add = $add->where($op); // sub where group
            $op = 'OR';
        }
        foreach((array) $value as $item) {
            $pl = $QB->addValue($item);
            $add->where($op, "$col $comp $pl");
        }
    }

    /**
     * When sorting `%lastupdated%`, then sort the data from the `titles` table instead the `data_` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $order
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order) {
        $col = "$tablealias.$colname";

        if(is_a($this->context,'dokuwiki\plugin\struct\meta\RevisionColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
            $col = "$rightalias.lastrev";
        }

        $QB->addOrderBy("$col $order");
    }

}
