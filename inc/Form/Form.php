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
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setHiddenField($name, $value) {
        $this->hidden[$name] = $value;
        return $this;
    }

    #region element query function

    /**
     * Returns the numbers of elements in the form
     *
     * @return int
     */
    public function elementCount() {
        return count($this->elements);
    }

    /**
     * Returns a reference to the element at a position.
     * A position out-of-bounds will return either the
     * first (underflow) or last (overflow) element.
     *
     * @param int $pos
     * @return Element
     */
    public function getElementAt($pos) {
        if($pos < 0) $pos = count($this->elements) + $pos;
        if($pos < 0) $pos = 0;
        if($pos >= count($this->elements)) $pos = count($this->elements) - 1;
        return $this->elements[$pos];
    }

    /**
     * Gets the position of the first of a type of element
     *
     * @param string $type Element type to look for.
     * @param int $offset search from this position onward
     * @return false|int position of element if found, otherwise false
     */
    public function findPositionByType($type, $offset = 0) {
        $len = $this->elementCount();
        for($pos = $offset; $pos < $len; $pos++) {
            if($this->elements[$pos]->getType() == $type) {
                return $pos;
            }
        }
        return false;
    }

    /**
     * Gets the position of the first element matching the attribute
     *
     * @param string $name Name of the attribute
     * @param string $value Value the attribute should have
     * @param int $offset search from this position onward
     * @return false|int position of element if found, otherwise false
     */
    public function findPositionByAttribute($name, $value, $offset = 0) {
        $len = $this->elementCount();
        for($pos = $offset; $pos < $len; $pos++) {
            if($this->elements[$pos]->attr($name) == $value) {
                return $pos;
            }
        }
        return false;
    }

    #endregion

    #region Element positioning functions

    /**
     * Adds or inserts an element to the form
     *
     * @param Element $element
     * @param int $pos 0-based position in the form, -1 for at the end
     * @return Element
     */
    public function addElement(Element $element, $pos = -1) {
        if(is_a($element, '\dokuwiki\Form\Form')) throw new \InvalidArgumentException('You can\'t add a form to a form');
        if($pos < 0) {
            $this->elements[] = $element;
        } else {
            array_splice($this->elements, $pos, 0, array($element));
        }
        return $element;
    }

    /**
     * Replaces an existing element with a new one
     *
     * @param Element $element the new element
     * @param int $pos 0-based position of the element to replace
     */
    public function replaceElement(Element $element, $pos) {
        if(is_a($element, '\dokuwiki\Form\Form')) throw new \InvalidArgumentException('You can\'t add a form to a form');
        array_splice($this->elements, $pos, 1, array($element));
    }

    /**
     * Remove an element from the form completely
     *
     * @param int $pos 0-based position of the element to remove
     */
    public function removeElement($pos) {
        array_splice($this->elements, $pos, 1);
    }

    #endregion

    #region Element adding functions

    /**
     * Adds a text input field
     *
     * @param string $name
     * @param string $label
     * @param int $pos
     * @return InputElement
     */
    public function addTextInput($name, $label = '', $pos = -1) {
        return $this->addElement(new InputElement('text', $name, $label), $pos);
    }

    /**
     * Adds a password input field
     *
     * @param string $name
     * @param string $label
     * @param int $pos
     * @return InputElement
     */
    public function addPasswordInput($name, $label = '', $pos = -1) {
        return $this->addElement(new InputElement('password', $name, $label), $pos);
    }

    /**
     * Adds a radio button field
     *
     * @param string $name
     * @param string $label
     * @param int $pos
     * @return CheckableElement
     */
    public function addRadioButton($name, $label = '', $pos = -1) {
        return $this->addElement(new CheckableElement('radio', $name, $label), $pos);
    }

    /**
     * Adds a checkbox field
     *
     * @param string $name
     * @param string $label
     * @param int $pos
     * @return CheckableElement
     */
    public function addCheckbox($name, $label = '', $pos = -1) {
        return $this->addElement(new CheckableElement('checkbox', $name, $label), $pos);
    }

    /**
     * Adds a dropdown field
     *
     * @param string $name
     * @param array $options
     * @param string $label
     * @param int $pos
     * @return DropdownElement
     */
    public function addDropdown($name, $options, $label = '', $pos = -1) {
        return $this->addElement(new DropdownElement($name, $options, $label), $pos);
    }

    /**
     * Adds a textarea field
     *
     * @param string $name
     * @param string $label
     * @param int $pos
     * @return TextareaElement
     */
    public function addTextarea($name, $label = '', $pos = -1) {
        return $this->addElement(new TextareaElement($name, $label), $pos);
    }

    /**
     * Adds a simple button, escapes the content for you
     *
     * @param string $name
     * @param string $content
     * @param int $pos
     * @return Element
     */
    public function addButton($name, $content, $pos = -1) {
        return $this->addElement(new ButtonElement($name, hsc($content)), $pos);
    }

    /**
     * Adds a simple button, allows HTML for content
     *
     * @param string $name
     * @param string $html
     * @param int $pos
     * @return Element
     */
    public function addButtonHTML($name, $html, $pos = -1) {
        return $this->addElement(new ButtonElement($name, $html), $pos);
    }

    /**
     * Adds a label referencing another input element, escapes the label for you
     *
     * @param string $label
     * @param string $for
     * @param int $pos
     * @return Element
     */
    public function addLabel($label, $for='', $pos = -1) {
        return $this->addLabelHTML(hsc($label), $for, $pos);
    }

    /**
     * Adds a label referencing another input element, allows HTML for content
     *
     * @param string $content
     * @param string|Element $for
     * @param int $pos
     * @return Element
     */
    public function addLabelHTML($content, $for='', $pos = -1) {
        $element = new LabelElement(hsc($content));

        if(is_a($for, '\dokuwiki\Form\Element')) {
            /** @var Element $for */
            $for = $for->id();
        }
        $for = (string) $for;
        if($for !== '') {
            $element->attr('for', $for);
        }

        return $this->addElement($element, $pos);
    }

    /**
     * Add fixed HTML to the form
     *
     * @param string $html
     * @param int $pos
     * @return HTMLElement
     */
    public function addHTML($html, $pos = -1) {
        return $this->addElement(new HTMLElement($html), $pos);
    }

    /**
     * Add a closed HTML tag to the form
     *
     * @param string $tag
     * @param int $pos
     * @return TagElement
     */
    public function addTag($tag, $pos = -1) {
        return $this->addElement(new TagElement($tag), $pos);
    }

    /**
     * Add an open HTML tag to the form
     *
     * Be sure to close it again!
     *
     * @param string $tag
     * @param int $pos
     * @return TagOpenElement
     */
    public function addTagOpen($tag, $pos = -1) {
        return $this->addElement(new TagOpenElement($tag), $pos);
    }

    /**
     * Add a closing HTML tag to the form
     *
     * Be sure it had been opened before
     *
     * @param string $tag
     * @param int $pos
     * @return TagCloseElement
     */
    public function addTagClose($tag, $pos = -1) {
        return $this->addElement(new TagCloseElement($tag), $pos);
    }

    /**
     * Open a Fieldset
     *
     * @param string $legend
     * @param int $pos
     * @return FieldsetOpenElement
     */
    public function addFieldsetOpen($legend = '', $pos = -1) {
        return $this->addElement(new FieldsetOpenElement($legend), $pos);
    }

    /**
     * Close a fieldset
     *
     * @param int $pos
     * @return TagCloseElement
     */
    public function addFieldsetClose($pos = -1) {
        return $this->addElement(new FieldsetCloseElement(), $pos);
    }

    #endregion

    /**
     * Adjust the elements so that fieldset open and closes are matching
     */
    protected function balanceFieldsets() {
        $lastclose = 0;
        $isopen = false;
        $len = count($this->elements);

        for($pos = 0; $pos < $len; $pos++) {
            $type = $this->elements[$pos]->getType();
            if($type == 'fieldsetopen') {
                if($isopen) {
                    //close previous fieldset
                    $this->addFieldsetClose($pos);
                    $lastclose = $pos + 1;
                    $pos++;
                    $len++;
                }
                $isopen = true;
            } else if($type == 'fieldsetclose') {
                if(!$isopen) {
                    // make sure there was a fieldsetopen
                    // either right after the last close or at the begining
                    $this->addFieldsetOpen('', $lastclose);
                    $len++;
                    $pos++;
                }
                $lastclose = $pos;
                $isopen = false;
            }
        }

        // close open fieldset at the end
        if($isopen) {
            $this->addFieldsetClose();
        }
    }

    /**
     * The HTML representation of the whole form
     *
     * @return string
     */
    public function toHTML() {
        $this->balanceFieldsets();

        $html = '<form ' . buildAttributes($this->attrs()) . '>';

        foreach($this->hidden as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . formText($value) . '" />';
        }

        foreach($this->elements as $element) {
            $html .= $element->toHTML();
        }

        $html .= '</form>';

        return $html;
    }
}
