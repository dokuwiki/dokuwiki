<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;

class Text extends AbstractMultiBaseType {

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
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
        $R->cdata($this->config['prefix'] . $value . $this->config['postfix']);
        return true;
    }

    /**
     * Comparisons are done against the full string (including prefix/postfix)
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string $value
     * @param string $op
     */
    public function filter(QueryBuilder $QB, $tablealias, $colname, $comp, $value, $op) {
        $column = "$tablealias.$colname";

        if ($this->config['prefix']) {
            $pl = $QB->addValue($this->config['prefix']);
            $column = "$pl || $column";
        }
        if ($this->config['postfix']) {
            $pl = $QB->addValue($this->config['postfix']);
            $column = "$column || $pl";
        }

        $pl = $QB->addValue($value);
        $QB->filters()->where($op, "$column $comp $pl");
    }

}
