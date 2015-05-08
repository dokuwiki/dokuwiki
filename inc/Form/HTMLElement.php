<?php
namespace dokuwiki\Form;

/**
 * Class HTMLElement
 *
 * Holds arbitrary HTML that is added as is to the Form
 *
 * @package dokuwiki\Form
 */
class HTMLElement extends Element {

    /**
     * @var string the raw HTML held by this element
     */
    protected $html = '';

    /**
     * @param string $html
     */
    public function __construct($html) {
        parent::__construct('html');
        $this->val($html);
    }

    /**
     * Get or set the element's content
     *
     * @param null|string $html
     * @return string|$this
     */
    public function val($html = null) {
        if($html !== null) {
            $this->html = $html;
            return $this;
        }
        return $this->html;
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
