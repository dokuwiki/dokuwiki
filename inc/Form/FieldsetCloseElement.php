<?php
namespace dokuwiki\Form;

/**
 * Class FieldsetCloseElement
 *
 * Closes an open Fieldset
 *
 * @package dokuwiki\Form
 */
class FieldsetCloseElement extends TagCloseElement {

    /**
     * @param array $attributes
     */
    public function __construct($attributes = array()) {
        parent::__construct('tagclose', $attributes);
    }

    /**
     * do not call this
     *
     * @param $class
     * @return void
     * @throws \BadMethodCallException
     */
    public function addClass($class) {
        throw new \BadMethodCallException('You can\t add classes to closing tag');
    }

    /**
     * do not call this
     *
     * @param $id
     * @return void
     * @throws \BadMethodCallException
     */
    public function id($id = null) {
        throw new \BadMethodCallException('You can\t add ID to closing tag');
    }

    /**
     * do not call this
     *
     * @param $name
     * @param $value
     * @return void
     * @throws \BadMethodCallException
     */
    public function attr($name, $value = null) {
        throw new \BadMethodCallException('You can\t add attributes to closing tag');
    }

    /**
     * do not call this
     *
     * @param $attributes
     * @return void
     * @throws \BadMethodCallException
     */
    public function attrs($attributes = null) {
        throw new \BadMethodCallException('You can\t add attributes to closing tag');
    }
}
