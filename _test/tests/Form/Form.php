<?php

namespace dokuwiki\test\Form;

/**
 * makes form internals accessible for testing
 */
class Form extends \dokuwiki\Form\Form
{
    /**
     * @return array list of element types
     */
    function getElementTypeList()
    {
        $list = array();
        foreach ($this->elements as $element) {
            $list[] = $element->getType();
        }
        return $list;
    }

    /** @inheritdoc */
    public function balanceFieldsets()
    {
        parent::balanceFieldsets();
    }
}
