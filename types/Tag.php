<?php

namespace plugin\struct\types;

use plugin\struct\meta\Search;
use plugin\struct\meta\SearchConfigParameters;
use plugin\struct\meta\Value;

class Tag extends AbstractMultiBaseType {

    protected $config = array(
        'page' => '',
        'autocomplete' => array(
            'mininput' => 2,
            'maxresult' => 5,
        ),
    );

    /**
     * @param int|string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $context = $this->getContext();
        $filter = SearchConfigParameters::$PARAM_FILTER . '[' . $context->getTable() . '.' . $context->getLabel() . '*~]=' . $value;

        $R->internallink($this->config['page'] . '?' . $filter, $value);
        return true;
    }

    public function handleAjax() {
        global $INPUT;

        // check minimum length
        $lookup = trim($INPUT->str('search'));
        if(utf8_strlen($lookup) < $this->config['autocomplete']['mininput']) return array();

        // results wanted?
        $max = $this->config['autocomplete']['maxresult'];
        if($max <= 0) return array();

        $context = $this->getContext();

        // lookup other values
        $search = new Search();
        $search->addSchema($context->getTable());
        $search->addColumn($context->getLabel());
        $search->addFilter($context->getLabel(), "$lookup%", '~');
        $search->addSort($context->getLabel());
        $search->setLimit($max);
        $search->setDistinct(true);
        $values = $search->execute();

        $result = array();
        /** @var Value[] $row */
        foreach($values as $row) {
            $result[] = array(
                'label' => $row[0]->getValue(),
                'value' => $row[0]->getValue(),
            );
        }

        return $result;
    }

}
