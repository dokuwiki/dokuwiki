<?php
namespace dokuwiki\Form;

/**
 * Class TextareaElement
 * @package dokuwiki\Form
 */
class TextareaElement extends InputElement {

    /**
     * @var string the actual text within the area
     */
    protected $text;

    /**
     * @param string $name The name of this form element
     * @param string $label The label text for this element
     */
    public function __construct($name, $label) {
        parent::__construct('textarea', $name, $label);
        $this->attr('dir', 'auto');
    }

    /**
     * Get or set the element's value
     *
     * This is the preferred way of setting the element's value
     *
     * @param null|string $value
     * @return string|$this
     */
    public function val($value = null) {
        if($value !== null) {
            $this->text = cleanText($value);
            return $this;
        }
        return $this->text;
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    protected function mainElementHTML() {
        if($this->useInput) $this->prefillInput();
        return '<textarea ' . buildAttributes($this->attrs()) . '>' .
        formText($this->val()) . '</textarea>';
    }

}
