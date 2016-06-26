<?php
namespace dokuwiki\Form;

/**
 * Class HTMLElement
 *
 * Holds arbitrary HTML that is added as is to the Form
 *
 * @package dokuwiki\Form
 */
class HTMLElement extends ValueElement {


    /**
     * @param string $html
     */
    public function __construct($html) {
        parent::__construct('html', $html);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return $this->val();
    }
}
