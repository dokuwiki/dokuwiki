<?php

namespace dokuwiki\Form;

class OptGroup extends Element
{
    protected $options = [];
    protected $values = [];

    /**
     * @param string $label The label text for this element (will be autoescaped)
     * @param array $options The available options
     */
    public function __construct($label, $options)
    {
        parent::__construct('optGroup', ['label' => $label]);
        $this->options($options);
    }

    /**
     * Store the given values so they can be used during rendering
     *
     * This is intended to be only called from within DropdownElement::val()
     *
     * @param string[] $values the values to set
     * @return string[] the values that have been set (options exist)
     * @see DropdownElement::val()
     */
    public function storeValues($values)
    {
        $this->values = [];
        foreach ($values as $value) {
            if (isset($this->options[$value])) {
                $this->values[] = $value;
            }
        }

        return $this->values;
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
    public function options($options = null)
    {
        if ($options === null) return $this->options;
        if (!is_array($options)) throw new \InvalidArgumentException('Options have to be an array');
        $this->options = [];
        foreach ($options as $key => $val) {
            if (is_array($val)) {
                if (!array_key_exists('label', $val)) {
                    throw new \InvalidArgumentException(
                        'If option is given as array, it has to have a "label"-key!'
                    );
                }
                if (
                    array_key_exists('attrs', $val) &&
                    is_array($val['attrs']) &&
                    array_key_exists('selected', $val['attrs'])
                ) {
                    throw new \InvalidArgumentException(
                        'Please use function "DropdownElement::val()" to set the selected option'
                    );
                }
                $this->options[$key] = $val;
            } elseif (is_int($key)) {
                $this->options[$val] = ['label' => (string)$val];
            } else {
                $this->options[$key] = ['label' => (string)$val];
            }
        }
        return $this;
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML()
    {
        if ($this->attributes['label'] === null) {
            return $this->renderOptions();
        }
        $html = '<optgroup ' . buildAttributes($this->attrs()) . '>';
        $html .= $this->renderOptions();
        $html .= '</optgroup>';
        return $html;
    }

    /**
     * @return string
     */
    protected function renderOptions()
    {
        $html = '';
        foreach ($this->options as $key => $val) {
            $selected = in_array((string)$key, $this->values) ? ' selected="selected"' : '';
            $attrs = '';
            if (!empty($val['attrs']) && is_array($val['attrs'])) {
                $attrs = buildAttributes($val['attrs']);
            }
            $html .= '<option' . $selected . ' value="' . hsc($key) . '" ' . $attrs . '>';
            $html .= hsc($val['label']);
            $html .= '</option>';
        }
        return $html;
    }
}
