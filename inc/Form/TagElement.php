<?php
namespace dokuwiki\Form;

/**
 * Class TagElement
 *
 * Creates a self closing HTML tag
 *
 * @package dokuwiki\Form
 */
class TagElement extends ValueElement {

    /**
     * @param string $tag
     * @param array $attributes
     */
    public function __construct($tag, $attributes = array()) {
        parent::__construct('tag', $tag, $attributes);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '<'.$this->val().' '.buildAttributes($this->attrs()).' />';
    }
}
