<?php

namespace dokuwiki\Remote;

use dokuwiki\Remote\OpenApiDoc\DocBlockMethod;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

class ApiCall
{
    /** @var callable The method to be called for this endpoint */
    protected $method;

    /** @var bool Whether this call can be called without authentication */
    protected bool $isPublic = false;

    /** @var string The category this call belongs to */
    protected string $category;

    /** @var DocBlockMethod The meta data of this call as parsed from its doc block */
    protected $docs;

    /**
     * Make the given method available as an API call
     *
     * @param string|array $method Either [object,'method'] or 'function'
     * @param string $category The category this call belongs to
     */
    public function __construct($method, $category = '')
    {
        if (!is_callable($method)) {
            throw new InvalidArgumentException('Method is not callable');
        }

        $this->method = $method;
        $this->category = $category;
    }

    /**
     * Call the method
     *
     * Important: access/authentication checks need to be done before calling this!
     *
     * @param array $args
     * @return mixed
     */
    public function __invoke($args)
    {
        if (!array_is_list($args)) {
            $args = $this->namedArgsToPositional($args);
        }
        return call_user_func_array($this->method, $args);
    }

    /**
     * Access the method documentation
     *
     * This lazy loads the docs only when needed
     *
     * @return DocBlockMethod
     */
    public function getDocs()
    {
        if ($this->docs === null) {
            try {
                if (is_array($this->method)) {
                    $reflect = new ReflectionMethod($this->method[0], $this->method[1]);
                } else {
                    $reflect = new ReflectionFunction($this->method);
                }
                $this->docs = new DocBlockMethod($reflect);
            } catch (ReflectionException $e) {
                throw new RuntimeException('Failed to parse API method documentation', 0, $e);
            }
        }
        return $this->docs;
    }

    /**
     * Is this a public method?
     *
     * Public methods can be called without authentication
     *
     * @return bool
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set the public flag
     *
     * @param bool $isPublic
     * @return $this
     */
    public function setPublic(bool $isPublic = true)
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    /**
     * Get information about the argument of this call
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->getDocs()->getParameters();
    }

    /**
     * Get information about the return value of this call
     *
     * @return array
     */
    public function getReturn()
    {
        return $this->getDocs()->getReturn();
    }

    /**
     * Get the summary of this call
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->getDocs()->getSummary();
    }

    /**
     * Get the description of this call
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getDocs()->getDescription();
    }

    /**
     * Get the category of this call
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Converts named arguments to positional arguments
     *
     * @fixme with PHP 8 we can use named arguments directly using the spread operator
     * @param array $params
     * @return array
     */
    protected function namedArgsToPositional($params)
    {
        $args = [];

        foreach ($this->getDocs()->getParameters() as $arg => $arginfo) {
            if (isset($params[$arg])) {
                $args[] = $params[$arg];
            } elseif ($arginfo['optional'] && array_key_exists('default', $arginfo)) {
                $args[] = $arginfo['default'];
            } else {
                throw new InvalidArgumentException("Missing argument $arg");
            }
        }

        return $args;
    }
}
