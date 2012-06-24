<?php

/**
 * Encapsulates access to the $_REQUEST array, making sure used parameters are initialized and
 * have the correct type.
 *
 * All function access the $_REQUEST array by default, if you want to access $_POST or $_GET
 * explicitly use the $post and $get members.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class Input {

    /** @var PostInput Access $_POST parameters */
    public $post;
    /** @var GetInput Access $_GET parameters */
    public $get;

    protected $access;

    /**
     * Intilizes the Input class and it subcomponents
     */
    function __construct() {
        $this->access = &$_REQUEST;
        $this->post   = new PostInput();
        $this->get    = new GetInput();
    }

    /**
     * Check if a parameter was set
     *
     * Basically a wrapper around isset
     *
     * @see isset
     * @param string $name Parameter name
     * @return bool
     */
    public function has($name) {
        return isset($this->access[$name]);
    }

    /**
     * Access a request parameter without any type conversion
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return mixed
     */
    public function param($name, $default = null, $nonempty = false) {
        if(!isset($this->access[$name])) return $default;
        if($nonempty && empty($this->access[$name])) return $default;
        return $this->access[$name];
    }

    /**
     * Get a reference to a request parameter
     *
     * This avoids copying data in memory, when the parameter is not set it will be created
     * and intialized with the given $default value before a reference is returned
     *
     * @param string    $name Parameter name
     * @param mixed     $default Initialize parameter with if not set
     * @param bool      $nonempty Init with $default if parameter is set but empty()
     * @return &mixed
     */
    public function &ref($name, $default = '', $nonempty = false) {
        if(!isset($this->access[$name]) || ($nonempty && empty($this->access[$name]))) {
            $this->access[$name] = $default;
        }

        $ref = &$this->access[$name];
        return $ref;
    }

    /**
     * Access a request parameter as int
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set or is an array
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return int
     */
    public function int($name, $default = 0, $nonempty = false) {
        if(!isset($this->access[$name])) return $default;
        if(is_array($this->access[$name])) return $default;
        if($nonempty && empty($this->access[$name])) return $default;

        return (int) $this->access[$name];
    }

    /**
     * Access a request parameter as string
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set or is an array
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return string
     */
    public function str($name, $default = '', $nonempty = false) {
        if(!isset($this->access[$name])) return $default;
        if(is_array($this->access[$name])) return $default;
        if($nonempty && empty($this->access[$name])) return $default;

        return (string) $this->access[$name];
    }

    /**
     * Access a request parameter as bool
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return bool
     */
    public function bool($name, $default = '', $nonempty = false) {
        if(!isset($this->access[$name])) return $default;
        if($nonempty && empty($this->access[$name])) return $default;

        return (bool) $this->access[$name];
    }

    /**
     * Access a request parameter as array
     *
     * @param string    $name     Parameter name
     * @param mixed     $default  Default to return if parameter isn't set
     * @param bool      $nonempty Return $default if parameter is set but empty()
     * @return array
     */
    public function arr($name, $default = array(), $nonempty = false) {
        if(!isset($this->access[$name])) return $default;
        if($nonempty && empty($this->access[$name])) return $default;

        return (array) $this->access[$name];
    }

}

/**
 * Internal class used for $_POST access in Input class
 */
class PostInput extends Input {
    protected $access;

    /**
     * Initialize the $access array, remove subclass members
     */
    function __construct() {
        $this->access = &$_POST;
        unset ($this->post);
        unset ($this->get);
    }
}

/**
 * Internal class used for $_GET access in Input class
 */
class GetInput extends Input {
    protected $access;

    /**
     * Initialize the $access array, remove subclass members
     */
    function __construct() {
        $this->access = &$_GET;
        unset ($this->post);
        unset ($this->get);
    }
}