<?php
namespace dokuwiki\Form;

/**
 * Class Form
 *
 * Represents the whole Form. This is what you work on, and add Elements to
 *
 * @package dokuwiki\Form
 */
class Form extends Element {

    /**
     * @var array name value pairs for hidden values
     */
    protected $hidden = array();

    /**
     * @var Element[] the elements of the form
     */
    protected $elements = array();

    /**
     * Creates a new, empty form with some default attributes
     *
     * @param array $attributes
     */
    public function __construct($attributes = array()) {
        global $ID;

        parent::__construct('form', $attributes);

        // use the current URL as default action
        if(!$this->attr('action')) {
            $get = $_GET;
            if(isset($get['id'])) unset($get['id']);
            $self = wl($ID, $get, false, '&'); //attributes are escaped later
            $this->attr('action', $self);
        }

        // post is default
        if(!$this->attr('method')) {
            $this->attr('method', 'post');
        }

        // we like UTF-8
        if(!$this->attr('accept-charset')) {
            $this->attr('accept-charset', 'utf-8');
        }

        // add the security token by default
        $this->setHiddenField('sectok', getSecurityToken());

        // identify this as a new form based form in HTML
        $this->addClass('doku_form');
    }

    /**
     * Sets a hidden field
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function setHiddenField($name, $value) {
        $this->hidden[$name] = $value;
        return $this;
    }

    /**
     * Adds an element to the end of the form
     *
     * @param Element $element
     * @param int $pos 0-based position in the form, -1 for at the end
     * @return Element
     */
    public function addElement(Element $element, $pos = -1) {
        if(is_a($element, 'Doku_Form2')) throw new \InvalidArgumentException('You can\'t add a form to a form');
        if($pos < 0) {
            $this->elements[] = $element;
        } else {
            array_splice($this->elements, $pos, 0, array($element));
        }
        return $element;
    }

    /**
     * Adds a text input field
     *
     * @param $name
     * @param $label
     * @param int $pos
     * @return InputElement
     */
    public function addTextInput($name, $label = '', $pos = -1) {
        return $this->addElement(new InputElement('text', $name, $label), $pos);
    }

    /**
     * Adds a password input field
     *
     * @param $name
     * @param $label
     * @param int $pos
     * @return InputElement
     */
    public function addPasswordInput($name, $label = '', $pos = -1) {
        return $this->addElement(new InputElement('password', $name, $label), $pos);
    }

    /**
     * Adds a radio button field
     *
     * @param $name
     * @param $label
     * @param int $pos
     * @return CheckableElement
     */
    public function addRadioButton($name, $label = '', $pos = -1) {
        return $this->addElement(new CheckableElement('radio', $name, $label), $pos);
    }

    /**
     * Adds a checkbox field
     *
     * @param $name
     * @param $label
     * @param int $pos
     * @return CheckableElement
     */
    public function addCheckbox($name, $label = '', $pos = -1) {
        return $this->addElement(new CheckableElement('checkbox', $name, $label), $pos);
    }

    /**
     * Adds a textarea field
     *
     * @param $name
     * @param $label
     * @param int $pos
     * @return TextareaElement
     */
    public function addTextarea($name, $label = '', $pos = -1) {
        return $this->addElement(new TextareaElement($name, $label), $pos);
    }

    /**
     * Add fixed HTML to the form
     *
     * @param $html
     * @param int $pos
     * @return Element
     */
    public function addHTML($html, $pos = -1) {
        return $this->addElement(new HTMLElement($html), $pos);
    }

    protected function balanceFieldsets() {
        //todo implement!
    }

    /**
     * The HTML representation of the whole form
     *
     * @return string
     */
    public function toHTML() {
        $this->balanceFieldsets();

        $html = '<form ' . buildAttributes($this->attrs()) . '>' . DOKU_LF;

        foreach($this->hidden as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . formText($value) . '" />' . DOKU_LF;
        }

        foreach($this->elements as $element) {
            $html .= $element->toHTML() . DOKU_LF;
        }

        $html .= '</form>' . DOKU_LF;

        return $html;
    }
}
