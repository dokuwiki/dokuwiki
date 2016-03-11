<?php
namespace dokuwiki\Form;

/**
 * Class InputElement
 *
 * Base class for all input elements. Uses a wrapping label when label
 * text is given.
 *
 * @todo figure out how to make wrapping or related label configurable
 * @package dokuwiki\Form
 */
class InputElement extends Element {
    /**
     * @var LabelElement
     */
    protected $label = null;

    /**
     * @var bool if the element should reflect posted values
     */
    protected $useInput = true;

    /**
     * @param string $type The type of this element
     * @param string $name The name of this form element
     * @param string $label The label text for this element (will be autoescaped)
     */
    public function __construct($type, $name, $label = '') {
        parent::__construct($type, array('name' => $name));
        $this->attr('name', $name);
        $this->attr('type', $type);
        if($label) $this->label = new LabelElement($label);
    }

    /**
     * Returns the label element if there's one set
     *
     * @return LabelElement|null
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Should the user sent input be used to initialize the input field
     *
     * The default is true. Any set values will be overwritten by the INPUT
     * provided values.
     *
     * @param bool $useinput
     * @return $this
     */
    public function useInput($useinput) {
        $this->useInput = (bool) $useinput;
        return $this;
    }

    /**
     * Get or set the element's ID
     *
     * @param null|string $id
     * @return string|$this
     */
    public function id($id = null) {
        if($this->label) $this->label->attr('for', $id);
        return parent::id($id);
    }

    /**
     * Adds a class to the class attribute
     *
     * This is the preferred method of setting the element's class
     *
     * @param string $class the new class to add
     * @return $this
     */
    public function addClass($class) {
        if($this->label) $this->label->addClass($class);
        return parent::addClass($class);
    }

    /**
     * Figures out how to access the value for this field from INPUT data
     *
     * The element's name could have been given as a simple string ('foo')
     * or in array notation ('foo[bar]').
     *
     * Note: this function only handles one level of arrays. If your data
     * is nested deeper, you should call useInput(false) and set the
     * correct value yourself
     *
     * @return array name and array key (null if not an array)
     */
    protected function getInputName() {
        $name = $this->attr('name');
        parse_str("$name=1", $parsed);

        $name = array_keys($parsed);
        $name = array_shift($name);

        if(is_array($parsed[$name])) {
            $key = array_keys($parsed[$name]);
            $key = array_shift($key);
        } else {
            $key = null;
        }

        return array($name, $key);
    }

    /**
     * Handles the useInput flag and set the value attribute accordingly
     */
    protected function prefillInput() {
        global $INPUT;

        list($name, $key) = $this->getInputName();
        if(!$INPUT->has($name)) return;

        if($key === null) {
            $value = $INPUT->str($name);
        } else {
            $value = $INPUT->arr($name);
            if(isset($value[$key])) {
                $value = $value[$key];
            } else {
                $value = '';
            }
        }
        if($value !== '') {
            $this->val($value);
        }
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    protected function mainElementHTML() {
        if($this->useInput) $this->prefillInput();
        return '<input ' . buildAttributes($this->attrs()) . ' />';
    }

    /**
     * The HTML representation of this element wrapped in a label
     *
     * @return string
     */
    public function toHTML() {
        if($this->label) {
            return '<label ' . buildAttributes($this->label->attrs()) . '>' . DOKU_LF .
            '<span>' . hsc($this->label->val()) . '</span>' . DOKU_LF .
            $this->mainElementHTML() . DOKU_LF .
            '</label>';
        } else {
            return $this->mainElementHTML();
        }
    }
}
