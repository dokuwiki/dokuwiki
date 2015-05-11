<?php
namespace dokuwiki\Form;

/**
 * Class FieldsetCloseElement
 *
 * Closes an open Fieldset
 *
 * @package dokuwiki\Form
 */
class FieldsetCloseElement extends TagCloseElement {

    /**
     * @param array $attributes
     */
    public function __construct($attributes = array()) {
        parent::__construct('', $attributes);
        $this->type = 'fieldsetclose';
    }


    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '</fieldset>';
    }
}
