<?php
namespace dokuwiki\Form;

/**
 * Class TagCloseElement
 *
 * Creates an HTML close tag. You have to make sure it has been opened
 * before or this will produce invalid HTML
 *
 * @package dokuwiki\Form
 */
class TagCloseElement extends ValueElement {

    /**
     * @param string $tag
     * @param array $attributes
     */
    public function __construct($tag, $attributes = array()) {
        parent::__construct('tagclose', $tag, $attributes);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '</'.$this->val().'>';
    }
}
