<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\QueryBuilderWhere;

class LongText extends AbstractMultiBaseType {
    use TraitFilterPrefix;

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
        'rows' => '5',
        'cols' => '50'
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
     * Clean line endings
     *
     * @param int|string $rawvalue
     * @return int|string
     */
    public function validate($rawvalue) {
        $rawvalue = rtrim($rawvalue);
        $rawvalue = cleanText($rawvalue);
        return $rawvalue;
    }
    
    /**
     * Use a text area for input
     *
     * @param string $name
     * @param string $rawvalue
     * @param string $htmlID
     *
     * @return string
     */
    public function valueEditor($name, $rawvalue, $htmlID) {
        $rawvalue = formText($rawvalue);
        $params = array(
            'name' => $name,
            'class' => 'struct_'.strtolower($this->getClass()),
            'id' => $htmlID,
            'rows' => $this->config['rows'],
            'cols' => $this->config['cols']
        );
        $attributes = buildAttributes($params, true);

        return "<textarea $attributes>$rawvalue</textarea>";
    }
}
