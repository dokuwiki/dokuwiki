<?php
namespace dokuwiki\Form;

/**
 * Class Label
 * @package dokuwiki\Form
 */
class Label extends Element {
    /**
     * @var string the actual label text
     */
    public $label = '';

    /**
     * Creates a new Label
     *
     * @param string $label
     */
    public function __construct($label) {
        parent::__construct('label');
        $this->label = $label;
    }

    /**
     * Get or set the element's label text
     *
     * @param null|string $value
     * @return string|$this
     */
    public function val($value = null) {
        if($value !== null) {
            $this->label = $value;
            return $this;
        }
        return $this->label;
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '<label ' . buildAttributes($this->attrs()) . '>' . hsc($this->label) . '</label>';
    }
}
