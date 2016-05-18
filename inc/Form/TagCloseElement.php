<?php
namespace dokuwiki\Form;

/**
 * Class TagCloseElement
 *
 * Creates an HTML close tag. You have to make sure it has been opened
 * before or this will produce invalid HTML
 *
 * @package dokuwiki\Form
 */
class TagCloseElement extends ValueElement {

    /**
     * @param string $tag
     * @param array $attributes
     */
    public function __construct($tag, $attributes = array()) {
        parent::__construct('tagclose', $tag, $attributes);
    }

    /**
     * do not call this
     *
     * @param string $class
     * @return void
     * @throws \BadMethodCallException
     */
    public function addClass($class) {
        throw new \BadMethodCallException('You can\t add classes to closing tag');
    }

    /**
     * do not call this
     *
     * @param null|string $id
     * @return string
     * @throws \BadMethodCallException
     */
    public function id($id = null) {
        if ($id === null) {
            return '';
        } else {
            throw new \BadMethodCallException('You can\t add ID to closing tag');
        }
    }

    /**
     * do not call this
     *
     * @param string $name
     * @param null|string $value
     * @return string
     * @throws \BadMethodCallException
     */
    public function attr($name, $value = null) {
        if ($value === null) {
            return '';
        } else {
            throw new \BadMethodCallException('You can\t add attributes to closing tag');
        }
    }

    /**
     * do not call this
     *
     * @param array|null $attributes
     * @return array
     * @throws \BadMethodCallException
     */
    public function attrs($attributes = null) {
        if ($attributes === null) {
            return array();
        } else {
            throw new \BadMethodCallException('You can\t add attributes to closing tag');
        }
    }

    /**
     * The HTML representation of this element
     *
     * @return string
     */
    public function toHTML() {
        return '</'.$this->val().'>';
    }

}
