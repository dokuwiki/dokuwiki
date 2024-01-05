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
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     * @return $this
     */
    public function setPublic(bool $isPublic = true): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }


    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->getDocs()->getParameters();
    }

    /**
     * @return array
     */
    public function getReturn(): array
    {
        return $this->getDocs()->getReturn();
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->getDocs()->getSummary();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getDocs()->getDescription();
    }

    /**
     * @return string
     */
    public function getCategory(): string
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

        foreach (array_keys($this->getDocs()->getParameters()) as $arg) {
            if (isset($params[$arg])) {
                $args[] = $params[$arg];
            } else {
                $args[] = null;
            }
        }

        return $args;
    }

}
