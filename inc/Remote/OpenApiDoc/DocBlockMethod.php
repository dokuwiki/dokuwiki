<?php

namespace dokuwiki\Remote\OpenApiDoc;

use ReflectionFunction;
use ReflectionMethod;

class DocBlockMethod extends DocBlock
{
    /**
     * Parse the given docblock
     *
     * The docblock can be of a method, class or property.
     *
     * @param ReflectionMethod|ReflectionFunction $reflector
     */
    public function __construct($reflector)
    {
        parent::__construct($reflector);
        $this->refineParam();
        $this->refineReturn();
    }

    /** @inheritdoc */
    protected function getContext()
    {
        if ($this->reflector instanceof ReflectionFunction) {
            return null;
        }
        return parent::getContext();
    }

    /**
     * Convenience method to access the method parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->getTag('param');
    }

    /**
     * Convenience method to access the method return
     *
     * @return array
     */
    public function getReturn()
    {
        return $this->getTag('return');
    }

    /**
     * Parse the param tag into its components
     *
     * @return void
     */
    protected function refineParam()
    {
        $result = [];

        // prefill from reflection
        foreach ($this->reflector->getParameters() as $parameter) {
            $refType = $parameter->getType();
            $result[$parameter->getName()] = [
                'type' => new Type($refType ? $refType->getName() : 'string', $this->getContext()),
                'optional' => $parameter->isOptional(),
                'description' => '',
            ];
            if ($parameter->isDefaultValueAvailable()) {
                $result[$parameter->getName()]['default'] = $parameter->getDefaultValue();
            }
        }

        // refine from doc tags
        foreach ($this->tags['param'] ?? [] as $param) {
            [$type, $name, $description] = array_map('trim', sexplode(' ', $param, 3, ''));
            if ($name === '' || $name[0] !== '$') continue;
            $name = substr($name, 1);
            if (!isset($result[$name])) continue; // reflection says this param does not exist

            $result[$name]['type'] = new Type($type, $this->getContext());
            $result[$name]['description'] = $description;
        }
        $this->tags['param'] = $result;
    }

    /**
     * Parse the return tag into its components
     *
     * @return void
     */
    protected function refineReturn()
    {


        // prefill from reflection
        $refType = $this->reflector->getReturnType();
        $result = [
            'type' => new Type($refType ? $refType->getName() : 'void', $this->getContext()),
            'description' => '',
        ];

        // refine from doc tag
        foreach ($this->tags['return'] ?? [] as $return) {
            [$type, $description] = array_map('trim', sexplode(' ', $return, 2, ''));
            $result['type'] = new Type($type, $this->getContext());
            $result['description'] = $description;
        }
        $this->tags['return'] = $result;
    }
}
