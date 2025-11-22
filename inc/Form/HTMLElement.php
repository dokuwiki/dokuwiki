<?php

namespace easywiki\Form;

/**
 * Class HTMLElement
 *
 * Holds arbitrary HTML that is added as is to the Form
 *
 * @package easywiki\Form
 */
class HTMLElement extends ValueElement
{
    /**
     * @param string $html
     */
    public function __construct($html)
    {
        parent::__construct('html', $html);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML()
    {
        return $this->val();
    }
}
