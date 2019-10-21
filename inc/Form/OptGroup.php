<?php

namespace dokuwiki\Form;


class OptGroup extends Element {
    protected $options = array();
    protected $value;

    /**
     * @param string $label The label text for this element (will be autoescaped)
     * @param array  $options The available options
     */
    public function __construct($label, $options) {
        parent::__construct('optGroup', array('label' => $label));
        $this->options($options);
    }

    /**
     * Store the given value so it can be used during rendering
     *
     * This is intended to be only called from within @see DropdownElement::val()
     *
     * @param string $value
     * @return bool true if an option with the given value exists, false otherwise
     */
    public function storeValue($value) {
        $this->value = $value;
        return isset($this->options[$value]);
    }

    /**
     * Get or set the options of the optgroup
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
            if (is_array($val)) {
                if (!key_exists('label', $val)) throw new \InvalidArgumentException(
                    'If option is given as array, it has to have a "label"-key!'
                );
                if (key_exists('attrs', $val) && is_array($val['attrs']) && key_exists('selected', $val['attrs'])) {
                    throw new \InvalidArgumentException(
                        'Please use function "DropdownElement::val()" to set the selected option'
                    );
                }
                $this->options[$key] = $val;
            } elseif(is_int($key)) {
                $this->options[$val] = array('label' => (string) $val);
            } else {
                $this->options[$key] = array('label' => (string) $val);
            }
        }
        return $this;
    }


    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        if ($this->attributes['label'] === null) {
            return $this->renderOptions();
        }
        $html = '<optgroup '. buildAttributes($this->attrs()) . '>';
        $html .= $this->renderOptions();
        $html .= '</optgroup>';
        return $html;
    }


    /**
     * @return string
     */
    protected function renderOptions() {
        $html = '';
        foreach($this->options as $key => $val) {
            $selected = ((string)$key === (string)$this->value) ? ' selected="selected"' : '';
            $attrs = '';
            if (!empty($val['attrs']) && is_array($val['attrs'])) {
                $attrs = buildAttributes($val['attrs']);
            }
            $html .= '<option' . $selected . ' value="' . hsc($key) . '" '.$attrs.'>';
            $html .= hsc($val['label']);
            $html .= '</option>';
        }
        return $html;
    }
}
