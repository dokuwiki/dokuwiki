<?php
namespace dokuwiki\Form;

/**
 * Class DropdownElement
 *
 * Represents a HTML select. Please note that this does not support multiple selected options!
 *
 * @package dokuwiki\Form
 */
class DropdownElement extends InputElement {

    protected $options = array();

    protected $value = '';

    /**
     * @param string $name The name of this form element
     * @param string $options The available options
     * @param string $label The label text for this element (will be autoescaped)
     */
    public function __construct($name, $options, $label = '') {
        parent::__construct('dropdown', $name, $label);
        $this->options($options);
    }

    /**
     * Get or set the options of the Dropdown
     *
     * Options can be given as associative array (value => label) or as an
     * indexd array (label = value) or as an array of arrays. In the latter
     * case an element has to look as follows:
     * option-value => array (
     *                 'label' => option-label,
     *                 'attrs' => array (
     *                                    attr-key => attr-value, ...
     *                                  )
     *                 )
     *
     * @param null|array $options
     * @return $this|array
     */
    public function options($options = null) {
        if($options === null) return $this->options;
        if(!is_array($options)) throw new \InvalidArgumentException('Options have to be an array');
        $this->options = array();

        foreach($options as $key => $val) {
            if(is_int($key)) {
                $this->options[$val] = array('label' => (string) $val);
            } elseif (!is_array($val)) {
                $this->options[$key] = array('label' => (string) $val);
            } else {
                if (!key_exists('label', $val)) throw new \InvalidArgumentException('If option is given as array, it has to have a "label"-key!');
                $this->options[$key] = $val;
            }
        }
        $this->val(''); // set default value (empty or first)
        return $this;
    }

    /**
     * Gets or sets an attribute
     *
     * When no $value is given, the current content of the attribute is returned.
     * An empty string is returned for unset attributes.
     *
     * When a $value is given, the content is set to that value and the Element
     * itself is returned for easy chaining
     *
     * @param string $name Name of the attribute to access
     * @param null|string $value New value to set
     * @return string|$this
     */
    public function attr($name, $value = null) {
        if(strtolower($name) == 'multiple') {
            throw new \InvalidArgumentException('Sorry, the dropdown element does not support the "multiple" attribute');
        }
        return parent::attr($name, $value);
    }

    /**
     * Get or set the current value
     *
     * When setting a value that is not defined in the options, the value is ignored
     * and the first option's value is selected instead
     *
     * @param null|string $value The value to set
     * @return $this|string
     */
    public function val($value = null) {
        if($value === null) return $this->value;

        if(isset($this->options[$value])) {
            $this->value = $value;
        } else {
            // unknown value set, select first option instead
            $keys = array_keys($this->options);
            $this->value = (string) array_shift($keys);
        }

        return $this;
    }

    /**
     * Create the HTML for the select it self
     *
     * @return string
     */
    protected function mainElementHTML() {
        if($this->useInput) $this->prefillInput();

        $html = '<select ' . buildAttributes($this->attrs()) . '>';
        foreach($this->options as $key => $val) {
            $selected = ($key == $this->value) ? ' selected="selected"' : '';
            $attrs = '';
            if (is_array($val['attrs'])) {
                array_walk($val['attrs'],function (&$aval, $akey){$aval = hsc($akey).'="'.hsc($aval).'"';});
                $attrs = join(' ', $val['attrs']);
            }
            $html .= '<option' . $selected . ' value="' . hsc($key) . '" '.$attrs.'>' . hsc($val['label']) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

}
