<?php
namespace dokuwiki\Form;

/**
 * Class Label
 * @package dokuwiki\Form
 */
class LabelElement extends ValueElement {

    /**
     * Creates a new Label
     *
     * @param string $label This is is raw HTML and will not be escaped
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
        return '<label ' . buildAttributes($this->attrs()) . '>' . $this->val() . '</label>';
    }
}
