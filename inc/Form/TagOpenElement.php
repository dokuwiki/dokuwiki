<?php
namespace dokuwiki\Form;

/**
 * Class TagOpenElement
 *
 * Creates an open HTML tag. You have to make sure you close it
 * again or this will produce invalid HTML
 *
 * @package dokuwiki\Form
 */
class TagOpenElement extends ValueElement {

    /**
     * @param string $tag
     * @param array $attributes
     */
    public function __construct($tag, $attributes = array()) {
        parent::__construct('tagopen', $tag, $attributes);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '<'.$this->val().' '.buildAttributes($this->attrs()).'>';
    }
}
