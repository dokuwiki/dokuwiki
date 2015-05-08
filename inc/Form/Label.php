<?php
namespace dokuwiki\Form;

/**
 * Class Label
 * @package dokuwiki\Form
 */
class Label extends ValueElement {

    /**
     * Creates a new Label
     *
     * @param string $label
     */
    public function __construct($label) {
        parent::__construct('label', $label);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '<label ' . buildAttributes($this->attrs()) . '>' . hsc($this->val()) . '</label>';
    }
}
