<?php
namespace dokuwiki\Form;

/**
 * Class Element
 *
 * The basic building block of a form
 *
 * @package dokuwiki\Form
 */
abstract class Element {

    /**
     * @var array the attributes of this element
     */
    protected $attributes = array();

    /**
     * @var string The type of this element
     */
    protected $type;

    /**
     * @param string $type The type of this element
     * @param array $attributes
     */
    public function __construct($type, $attributes = array()) {
        $this->type = $type;
        $this->attributes = $attributes;
    }

    /**
     * Type of this element
     *
     * @return string
     */
    public function getType() {
        return $this->type;
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
        // set
        if($value !== null) {
            $this->attributes[$name] = $value;
            return $this;
        }

        // get
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            return '';
        }
    }

    /**
     * Removes the given attribute if it exists
     *
     * @param string $name
     * @return $this
     */
    public function rmattr($name) {
        if(isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
        return $this;
    }

    /**
     * Gets or adds a all given attributes at once
     *
     * @param array|null $attributes
     * @return array|$this
     */
    public function attrs($attributes = null) {
        // set
        if($attributes) {
            foreach((array) $attributes as $key => $val) {
                $this->attr($key, $val);
            }
            return $this;
        }
        // get
        return $this->attributes;
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
        $classes = explode(' ', $this->attr('class'));
        $classes[] = $class;
        $classes = array_unique($classes);
        $classes = array_filter($classes);
        $this->attr('class', join(' ', $classes));
        return $this;
    }

    /**
     * Get or set the element's ID
     *
     * This is the preferred way of setting the element's ID
     *
     * @param null|string $id
     * @return string|$this
     */
    public function id($id = null) {
        if(strpos($id, '__') === false) {
            throw new \InvalidArgumentException('IDs in DokuWiki have to contain two subsequent underscores');
        }

        return $this->attr('id', $id);
    }

    /**
     * Get or set the element's value
     *
     * This is the preferred way of setting the element's value
     *
     * @param null|string $value
     * @return string|$this
     */
    public function val($value = null) {
        return $this->attr('value', $value);
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    abstract public function toHTML();
}
