<?php

namespace dokuwiki\Form;

/**
 * Class DropdownElement
 *
 * Represents a HTML select. Please not that prefilling with input data only works for single values.
 *
 * @package dokuwiki\Form
 */
class DropdownElement extends InputElement
{
    /** @var array OptGroup[] */
    protected $optGroups = [];

    /** @var string[] the currently set values */
    protected $values = [];

    /**
     * @param string $name The name of this form element
     * @param array $options The available options
     * @param string $label The label text for this element (will be autoescaped)
     */
    public function __construct($name, $options, $label = '')
    {
        parent::__construct('dropdown', $name, $label);
        $this->rmattr('type');
        $this->optGroups[''] = new OptGroup(null, $options);
        $this->val('');
    }

    /**
     * Add an `<optgroup>` and respective options
     *
     * @param string $label
     * @param array $options
     * @return OptGroup a reference to the added optgroup
     * @throws \InvalidArgumentException
     */
    public function addOptGroup($label, $options)
    {
        if (empty($label)) {
            throw new \InvalidArgumentException(hsc('<optgroup> must have a label!'));
        }
        $this->optGroups[$label] = new OptGroup($label, $options);
        return end($this->optGroups);
    }

    /**
     * Set or get the optgroups of an Dropdown-Element.
     *
     * optgroups have to be given as associative array
     *   * the key being the label of the group
     *   * the value being an array of options as defined in @param null|array $optGroups
     * @return OptGroup[]|DropdownElement
     * @see OptGroup::options()
     *
     */
    public function optGroups($optGroups = null)
    {
        if ($optGroups === null) {
            return $this->optGroups;
        }
        if (!is_array($optGroups)) {
            throw new \InvalidArgumentException(hsc('Argument must be an associative array of label => [options]!'));
        }
        $this->optGroups = [];
        foreach ($optGroups as $label => $options) {
            $this->addOptGroup($label, $options);
        }
        return $this;
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
    public function options($options = null)
    {
        if ($options === null) {
            return $this->optGroups['']->options();
        }
        $this->optGroups[''] = new OptGroup(null, $options);
        return $this;
    }

    /**
     * Get or set the current value
     *
     * When setting a value that is not defined in the options, the value is ignored
     * and the first option's value is selected instead
     *
     * @param null|string|string[] $value The value to set
     * @return $this|string|string[]
     */
    public function val($value = null)
    {
        // getter
        if ($value === null) {
            if (isset($this->attributes['multiple'])) {
                return $this->values;
            } else {
                return $this->values[0];
            }
        }

        // setter
        $this->values = $this->setValuesInOptGroups((array) $value);
        if (!$this->values) {
            // unknown value set, select first option instead
            $this->values = $this->setValuesInOptGroups((array) $this->getFirstOptionKey());
        }

        return $this;
    }

    /**
     * Returns the first option's key
     *
     * @return string
     */
    protected function getFirstOptionKey()
    {
        $options = $this->options();
        if (!empty($options)) {
            $keys = array_keys($options);
            return (string)array_shift($keys);
        }
        foreach ($this->optGroups as $optGroup) {
            $options = $optGroup->options();
            if (!empty($options)) {
                $keys = array_keys($options);
                return (string)array_shift($keys);
            }
        }

        return ''; // should not happen
    }

    /**
     * Set the value in the OptGroups, including the optgroup for the options without optgroup.
     *
     * @param string[] $values The values to be set
     * @return string[] The values actually set
     */
    protected function setValuesInOptGroups($values)
    {
        $valueset = [];

        /** @var OptGroup $optGroup */
        foreach ($this->optGroups as $optGroup) {
            $found = $optGroup->storeValues($values);
            $values = array_diff($values, $found);
            $valueset = array_merge($valueset, $found);
        }

        return $valueset;
    }

    /**
     * Create the HTML for the select it self
     *
     * @return string
     */
    protected function mainElementHTML()
    {
        $attr = $this->attrs();
        if (isset($attr['multiple'])) {
            // use array notation when multiple values are allowed
            $attr['name'] .= '[]';
        } elseif ($this->useInput) {
            // prefilling is only supported for non-multi fields
            $this->prefillInput();
        }

        $html = '<select ' . buildAttributes($attr) . '>';
        $html = array_reduce(
            $this->optGroups,
            static fn($html, OptGroup $optGroup) => $html . $optGroup->toHTML(),
            $html
        );
        $html .= '</select>';

        return $html;
    }
}
