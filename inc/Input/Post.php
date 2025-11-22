<?php

namespace easywiki\Input;

/**
 * Internal class used for $_POST access in easywiki\Input\Input class
 */
class Post extends Input
{
    /** @noinspection PhpMissingParentConstructorInspection
     * Initialize the $access array, remove subclass members
     */
    public function __construct()
    {
        $this->access = &$_POST;
    }

    /**
     * Sets a parameter in $_POST and $_REQUEST
     *
     * @param string $name Parameter name
     * @param mixed $value Value to set
     */
    public function set($name, $value)
    {
        parent::set($name, $value);
        $_REQUEST[$name] = $value;
    }
}
