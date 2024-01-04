<?php

namespace dokuwiki\Remote\OpenApiDoc;

class Type
{
    protected $typehint;
    protected $context;

    /**
     * @param string $typehint The typehint as read from the docblock
     * @param string $context A fully qualified class name in which context the typehint is used
     */
    public function __construct($typehint, $context = '')
    {
        $this->typehint = $typehint;
        $this->context = $context;
    }

    /**
     * Return the typehint as read from the docblock
     *
     * @return string
     */
    public function __toString()
    {
        return $this->typehint;
    }

    /**
     * Return a primitive PHP type
     *
     * @param string $typehint
     * @return string
     */
    protected function toPrimitiveType($typehint)
    {
        if (str_ends_with($typehint, '[]')) {
            return 'array';
        }

        if (in_array($typehint, ['boolean', 'false', 'true'])) {
            return 'bool';
        }

        if (in_array($typehint, ['integer', 'date'])) {
            return 'int';
        }

        if ($typehint === 'file') {
            return 'string';
        }

        // fully qualified class name
        if ($typehint[0] === '\\') {
            return ltrim($typehint, '\\');
        }

        // relative class name, try to resolve
        if ($this->context && ctype_upper($typehint[0])) {
            return ClassResolver::getInstance()->resolve($typehint, $this->context);
        }

        return $typehint;
    }

    /**
     * Return a primitive type understood by the XMLRPC server
     *
     * @param string $typehint
     * @return string
     */
    public function getJSONRPCType()
    {
        return $this->toPrimitiveType($this->typehint);
    }

    /**
     * If this is an array, return the type of the array elements
     *
     * @return Type|null null if this is not a typed array
     */
    public function getSubType()
    {
        $type = $this->typehint;
        if (!str_ends_with($type, '[]')) {
            return null;
        }
        $type = substr($type, 0, -2);
        return new Type($type, $this->context);
    }

    /**
     * Return a type understood by the XMLRPC server
     *
     * @return string
     */
    public function getXMLRPCType()
    {
        $type = $this->typehint;

        // keep custom types
        if (in_array($type, ['date', 'file', 'struct'])) {
            return $type;
        }

        $type = $this->toPrimitiveType($this->typehint);

        // primitive types
        if (in_array($type, ['int', 'string', 'double', 'bool', 'array'])) {
            return $type;
        }

        // everything else is an object
        return 'object'; //should this return 'struct'?
    }
}
