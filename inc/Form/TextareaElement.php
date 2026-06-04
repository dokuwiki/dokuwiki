<?php

namespace dokuwiki\Form;

/**
 * Class TextareaElement
 * @package dokuwiki\Form
 */
class TextareaElement extends InputElement
{
    /**
     * @var string the actual text within the area
     */
    protected $text;

    /**
     * @param string $name The name of this form element
     * @param string $label The label text for this element
     */
    public function __construct($name, $label)
    {
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
    public function val($value = null)
    {
        if ($value !== null) {
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
    protected function mainElementHTML()
    {
        if ($this->useInput) $this->prefillInput();
        // The browser's HTML parser ignores a single newline immediately
        // after a <textarea> start tag, so a value that itself begins with a
        // newline would lose it on round-trip. We emit a guard newline that
        // absorbs the one the browser drops, keeping the value intact.
        // See the HTML Standard, "in body" insertion mode (the LF right after
        // <textarea> is dropped as an authoring convenience):
        // https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inbody
        return '<textarea ' . buildAttributes($this->attrs()) . '>' . "\n" .
            formText($this->val()) . '</textarea>';
    }
}
