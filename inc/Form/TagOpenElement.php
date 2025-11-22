<?php

namespace easywiki\Form;

/**
 * Class TagOpenElement
 *
 * Creates an open HTML tag. You have to make sure you close it
 * again or this will produce invalid HTML
 *
 * @package easywiki\Form
 */
class TagOpenElement extends ValueElement
{
    /**
     * @param string $tag
     * @param array $attributes
     */
    public function __construct($tag, $attributes = [])
    {
        parent::__construct('tagopen', $tag, $attributes);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML()
    {
        return '<' . $this->val() . ' ' . buildAttributes($this->attrs()) . '>';
    }
}
