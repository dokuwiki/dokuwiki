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

    /** @var array OptGroup[] */
    protected $optGroups = array();

    /**
     * @param string $name The name of this form element
     * @param array  $options The available options
     * @param string $label The label text for this element (will be autoescaped)
     */
    public function __construct($name, $options, $label = '') {
        parent::__construct('dropdown', $name, $label);
        $this->rmattr('type');
        $this->optGroups[''] = new OptGroup(null, $options);
        $this->val('');
    }

    /**
     * Add an `<optgroup>` and respective options
     *
     * @param string $label
     * @param array  $options
     * @return OptGroup a reference to the added optgroup
     * @throws \Exception
     */
    public function addOptGroup($label, $options) {
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
     *   * the value being an array of options as defined in @see OptGroup::options()
     *
     * @param null|array $optGroups
     * @return OptGroup[]|DropdownElement
     */
    public function optGroups($optGroups = null) {
        if($optGroups === null) {
            return $this->optGroups;
        }
        if (!is_array($optGroups)) {
            throw new \InvalidArgumentException(hsc('Argument must be an associative array of label => [options]!'));
        }
        $this->optGroups = array();
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
    public function options($options = null) {
        if ($options === null) {
            return $this->optGroups['']->options();
        }
        $this->optGroups[''] = new OptGroup(null, $options);
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

        $value_exists = $this->setValueInOptGroups($value);

        if($value_exists) {
            $this->value = $value;
        } else {
            // unknown value set, select first option instead
            $this->value = $this->getFirstOption();
            $this->setValueInOptGroups($this->value);
        }

        return $this;
    }

    /**
     * Returns the first options as it will be rendered in HTML
     *
     * @return string
     */
    protected function getFirstOption() {
        $options = $this->options();
        if (!empty($options)) {
            $keys = array_keys($options);
            return (string) array_shift($keys);
        }
        foreach ($this->optGroups as $optGroup) {
            $options = $optGroup->options();
            if (!empty($options)) {
                $keys = array_keys($options);
                return (string) array_shift($keys);
            }
        }
    }

    /**
     * Set the value in the OptGroups, including the optgroup for the options without optgroup.
     *
     * @param string $value
     * @return bool
     */
    protected function setValueInOptGroups($value) {
        $value_exists = false;
        /** @var OptGroup $optGroup */
        foreach ($this->optGroups as $optGroup) {
            $value_exists = $optGroup->storeValue($value) || $value_exists;
            if ($value_exists) {
                $value = null;
            }
        }
        return $value_exists;
    }

    /**
     * Create the HTML for the select it self
     *
     * @return string
     */
    protected function mainElementHTML() {
        if($this->useInput) $this->prefillInput();

        $html = '<select ' . buildAttributes($this->attrs()) . '>';
        $html = array_reduce($this->optGroups, function($html, OptGroup $optGroup) {return $html . $optGroup->toHTML();}, $html);
        $html .= '</select>';

        return $html;
    }

}
