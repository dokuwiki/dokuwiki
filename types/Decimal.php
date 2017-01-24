<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\QueryBuilderWhere;
use dokuwiki\plugin\struct\meta\ValidationException;

/**
 * Class Decimal
 *
 * A field accepting decimal numbers
 *
 * @package dokuwiki\plugin\struct\types
 */
class Decimal extends AbstractMultiBaseType {

    protected $config = array(
        'min' => '',
        'max' => '',
        'roundto' => '-1',
        'decpoint' => '.',
        'thousands' => "\xE2\x80\xAF", // narrow no-break space
        'trimzeros' => true,
        'prefix' => '',
        'postfix' => ''
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
        if($this->config['roundto'] == -1) {
            $value = $this->formatWithoutRounding(
                $value,
                $this->config['decpoint'],
                $this->config['thousands']
            );
        } else {
            $value = floatval($value);
            $value = number_format(
                $value,
                $this->config['roundto'],
                $this->config['decpoint'],
                $this->config['thousands']
            );
        }
        if($this->config['trimzeros'] && (strpos($value, $this->config['decpoint']) !== false)) {
            $value = rtrim($value, '0');
            $value = rtrim($value, $this->config['decpoint']);
        }

        $R->cdata($this->config['prefix'] . $value . $this->config['postfix']);
        return true;
    }

    /**
     * @param int|string $rawvalue
     * @return int|string
     * @throws ValidationException
     */
    public function validate($rawvalue) {
        $rawvalue = parent::validate($rawvalue);
        $rawvalue = str_replace(',', '.', $rawvalue); // we accept both

        if((string) $rawvalue != (string) floatval($rawvalue)) {
            throw new ValidationException('Decimal needed');
        }

        if($this->config['min'] !== '' && floatval($rawvalue) < floatval($this->config['min'])) {
            throw new ValidationException('Decimal min', floatval($this->config['min']));
        }

        if($this->config['max'] !== '' && floatval($rawvalue) > floatval($this->config['max'])) {
            throw new ValidationException('Decimal max', floatval($this->config['max']));
        }

        return $rawvalue;
    }

    /**
     * Works like number_format but keeps the decimals as is
     *
     * @link http://php.net/manual/en/function.number-format.php#91047
     * @author info at daniel-marschall dot de
     * @param float $number
     * @param string $dec_point
     * @param string $thousands_sep
     * @return string
     */
    function formatWithoutRounding($number, $dec_point, $thousands_sep) {
        $was_neg = $number < 0; // Because +0 == -0

        $tmp = explode('.', $number);
        $out = number_format(abs(floatval($tmp[0])), 0, $dec_point, $thousands_sep);
        if(isset($tmp[1])) $out .= $dec_point . $tmp[1];

        if($was_neg) $out = "-$out";

        return $out;
    }

    /**
     * Decimals need to be casted to the proper type for sorting
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $order
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order) {
        $QB->addOrderBy("CAST($tablealias.$colname AS DECIMAL) $order");
    }

    /**
     * Decimals need to be casted to proper type for comparison
     *
     * @param QueryBuilderWhere $add
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilderWhere $add, $tablealias, $colname, $comp, $value, $op) {
        /** @var QueryBuilderWhere $add Where additionional queries are added to*/
        if(is_array($value)) {
            $add = $add->where($op); // sub where group
            $op = 'OR';
        }
        foreach((array) $value as $item) {
            $pl = $add->getQB()->addValue($item);
            $add->where($op, "CAST($tablealias.$colname AS DECIMAL) $comp CAST($pl AS DECIMAL)");
        }
    }



}
