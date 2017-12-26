<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilderWhere;

class Wiki extends AbstractBaseType {
    use TraitFilterPrefix;

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
        'rows' => '5',
        'cols' => '50'
    );

    /**
     * @param int|string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $value = $this->config['prefix'] . $value . $this->config['postfix'];
        $doc = p_render($mode, p_get_instructions($value), $info);
        $R->doc .= $doc; // FIXME this probably does not work for all renderers
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
