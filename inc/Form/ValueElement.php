<?php

namespace dokuwiki\Form;

/**
 * Class ValueElement
 *
 * Just like an Element but it's value is not part of its attributes
 *
 * What the value is (tag name, content, etc) is defined by the actual implementations
 *
 * @package dokuwiki\Form
 */
abstract class ValueElement extends Element {

    /**
     * @var string holds the element's value
     */
    protected $value = '';

    /**
     * @param string $type
     * @param array|string $value
     * @param array $attributes
     */
    public function __construct($type, $value, $attributes = array()) {
        parent::__construct($type, $attributes);
        $this->val($value);
    }

    /**
     * Get or set the element's value
     *
     * @param null|string $value
     * @return string|$this
     */
    public function val($value = null) {
        if($value !== null) {
            $this->value = $value;
            return $this;
        }
        return $this->value;
    }

}
