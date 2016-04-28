<?php
namespace dokuwiki\plugin\struct\types;

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
     * Comparisons should always be done against the full string
     *
     * @param string $column
     * @param string $comp
     * @param string $value
     * @return array
     */
    public function compare($column, $comp, $value) {
        $opt = array();
        if ($this->config['prefix']) {
            $column = "? || $column";
            $opt[] = $this->config['prefix'];
        }
        if ($this->config['postfix']) {
            $column = "$column || ?";
            $opt[] = $this->config['postfix'];
        }

        // this assumes knowledge about the parent implementation which is kinda bad
        // but avoids some code duplication
        list($sql) = parent::compare($column, $comp, $value);
        $opt[] = $value;

        return array($sql, $opt);
    }

}
