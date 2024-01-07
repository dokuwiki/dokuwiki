<?php

namespace dokuwiki\Remote\OpenApiDoc;

use ReflectionClass;

class DocBlockClass extends DocBlock
{
    /** @var DocBlockMethod[] */
    protected $methods = [];

    /** @var DocBlockProperty[] */
    protected $properties = [];

    /**
     * Parse the given docblock
     *
     * The docblock can be of a method, class or property.
     *
     * @param ReflectionClass $reflector
     */
    public function __construct(ReflectionClass $reflector)
    {
        parent::__construct($reflector);
    }

    /** @inheritdoc */
    protected function getContext()
    {
        return $this->reflector->getName();
    }

    /**
     * Get the public methods of this class
     *
     * @return DocBlockMethod[]
     */
    public function getMethodDocs()
    {
        if ($this->methods) return $this->methods;

        foreach ($this->reflector->getMethods() as $method) {
            /** @var \ReflectionMethod $method */
            if ($method->isConstructor()) continue;
            if ($method->isDestructor()) continue;
            if (!$method->isPublic()) continue;
            $this->methods[$method->getName()] = new DocBlockMethod($method);
        }

        return $this->methods;
    }

    /**
     * Get the public properties of this class
     *
     * @return DocBlockProperty[]
     */
    public function getPropertyDocs()
    {
        if ($this->properties) return $this->properties;

        foreach ($this->reflector->getProperties() as $property) {
            /** @var \ReflectionProperty $property */
            if (!$property->isPublic()) continue;
            $this->properties[$property->getName()] = new DocBlockProperty($property);
        }

        return $this->properties;
    }
}
