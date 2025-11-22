<?php

namespace easywiki\Input;

/**
 * Internal class used for $_SERVER access in easywiki\Input\Input class
 */
class Server extends Input
{
    /** @noinspection PhpMissingParentConstructorInspection
     * Initialize the $access array, remove subclass members
     */
    public function __construct()
    {
        $this->access = &$_SERVER;
    }
}
