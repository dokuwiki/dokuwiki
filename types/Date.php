<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\ValidationException;

class Date extends AbstractBaseType {

    protected $config = array(
        'format' => 'Y/m/d',
        'prefilltoday' => false
    );

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $date = date_create($value);
        if($date !== false) {
            $out = date_format($date, $this->config['format']);
        } else {
            $out = '';
        }

        $R->cdata($out);
        return true;
    }

    /**
     * Return the editor to edit a single value
     *
     * @param string $name the form name where this has to be stored
     * @param string $rawvalue the current value
     * @return string html
     */
    public function valueEditor($name, $rawvalue) {
        $name = hsc($name);
        $rawvalue = hsc($rawvalue);

        if($this->config['prefilltoday'] && !$rawvalue) {
            $rawvalue = date('Y-m-d');
        }

        $html = "<input class=\"struct_date\" name=\"$name\" value=\"$rawvalue\" />";
        return "$html";
    }

    /**
     * Validate a single value
     *
     * This function needs to throw a validation exception when validation fails.
     * The exception message will be prefixed by the appropriate field on output
     *
     * @param string|int $rawvalue
     * @return int|string
     * @throws ValidationException
     */
    public function validate($rawvalue) {
        $rawvalue = parent::validate($rawvalue);
        list($rawvalue) = explode(' ', $rawvalue, 2); // strip off time if there is any

        list($year, $month, $day) = explode('-', $rawvalue, 3);
        if(!checkdate((int) $month, (int) $day, (int) $year)) {
            throw new ValidationException('invalid date format');
        }
        return sprintf('%d-%02d-%02d', $year, $month, $day);
    }

    /**
     * When handling `%lastupdated%` get the data from the `titles` table instead the `data_` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $alias
     */
    public function select(QueryBuilder $QB, $tablealias, $colname, $alias) {
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\RevisionColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
            $QB->addSelectStatement("$rightalias.lastrev", $alias);
            return;
        }

        parent::select($QB, $tablealias, $colname, $alias);
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
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\RevisionColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
            $QB->addOrderBy("$rightalias.lastrev $order");
            return;
        }

        $QB->addOrderBy("$tablealias.$colname $order");
    }

    /**
     * When using `%lastupdated%`, we need to compare against the `title` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilder $QB, $tablealias, $colname, $comp, $value, $op) {
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\RevisionColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");

            // compare against page and title
            $sub = $QB->filters()->where($op);
            $pl = $QB->addValue($value);
            $sub->whereOr("$rightalias.lastrev $comp $pl");
            return;
        }

        parent::filter($QB, $tablealias, $colname, $comp, $value, $op);
    }

}
