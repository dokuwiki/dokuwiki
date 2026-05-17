<?php

namespace dokuwiki\Remote\OpenApiDoc;

class Type implements \Stringable
{
    protected $typehint;
    protected $context;

    /** @var Type[]|null Lazily populated. Single-element [$this] when not a union. */
    protected ?array $unionMembers = null;
    protected bool $unionParsed = false;

    /** @var Type|null Key type for array<K, V>; null when not a map. */
    protected ?Type $mapKeyType = null;
    /** @var Type|null Value type for array<K, V>; null when not a map. */
    protected ?Type $mapValueType = null;
    protected bool $mapParsed = false;

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
    public function __toString(): string
    {
        return (string) $this->typehint;
    }

    /**
     * Return the base type
     *
     * This is the type this variable is. Eg. a string[] is an array.
     *
     * @return string
     */
    public function getBaseType()
    {
        $typehint = $this->typehint;

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
        return $this->getBaseType();
    }

    /**
     * Get the base type as one of the supported OpenAPI types
     *
     * Formats (eg. int32 or double) are not supported
     *
     * @link https://swagger.io/docs/specification/data-models/data-types/
     * @return string
     */
    public function getOpenApiType()
    {
        return match ($this->getBaseType()) {
            'int' => 'integer',
            'bool' => 'boolean',
            'array' => 'array',
            'string', 'mixed' => 'string',
            'double', 'float' => 'number',
            default => 'object',
        };
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

        $type = $this->getBaseType($this->typehint);

        // primitive types
        if (in_array($type, ['int', 'string', 'double', 'bool', 'array'])) {
            return $type;
        }

        // everything else is an object
        return 'object'; //should this return 'struct'?
    }

    /**
     * Is this a union type like `int|bool` or `string|null`?
     *
     * @return bool
     */
    public function isUnion(): bool
    {
        return count($this->getUnionMembers()) > 1;
    }

    /**
     * Return the members of a union type, each as its own Type.
     *
     * For non-union types this returns a single-element array containing $this,
     * so callers can iterate uniformly without a separate isUnion check.
     *
     * @return Type[]
     */
    public function getUnionMembers(): array
    {
        if (!$this->unionParsed) {
            $this->parseUnion();
            $this->unionParsed = true;
        }
        return $this->unionMembers;
    }

    /**
     * Does this union include `null`?
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        foreach ($this->getUnionMembers() as $member) {
            if ((string) $member === 'null') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the union members excluding `null`. For non-nullable types this is the same as getUnionMembers().
     *
     * @return Type[]
     */
    public function getNonNullMembers(): array
    {
        $result = [];
        foreach ($this->getUnionMembers() as $member) {
            if ((string) $member !== 'null') {
                $result[] = $member;
            }
        }
        return $result;
    }

    /**
     * Is this a typed associative-array hint like `array<string, Page>`?
     *
     * Only the two-arg form is recognised. `array<T>` (list shorthand) is not a map.
     *
     * @return bool
     */
    public function isMap(): bool
    {
        $this->ensureMapParsed();
        return $this->mapKeyType !== null;
    }

    /**
     * Key type of an `array<K, V>` hint, or null if not a map.
     *
     * @return Type|null
     */
    public function getMapKeyType(): ?Type
    {
        $this->ensureMapParsed();
        return $this->mapKeyType;
    }

    /**
     * Value type of an `array<K, V>` hint, or null if not a map.
     *
     * @return Type|null
     */
    public function getMapValueType(): ?Type
    {
        $this->ensureMapParsed();
        return $this->mapValueType;
    }

    private function parseUnion(): void
    {
        $hint = trim($this->typehint);
        if (str_contains($hint, '|')) {
            $parts = $this->splitTopLevel($hint, '|');
            // Drop empty parts from malformed input like `int|` or `|int`
            $parts = array_values(array_filter(array_map('trim', $parts), fn($p) => $p !== ''));
            if (count($parts) > 1) {
                $this->unionMembers = [];
                foreach ($parts as $p) {
                    $this->unionMembers[] = new Type($p, $this->context);
                }
                return;
            }
        }
        $this->unionMembers = [$this];
    }

    private function ensureMapParsed(): void
    {
        if ($this->mapParsed) {
            return;
        }
        $this->mapParsed = true;
        $hint = trim($this->typehint);
        if (!preg_match('/^array<(.+)>$/i', $hint, $m)) {
            return;
        }
        $parts = $this->splitTopLevel($m[1], ',');
        if (count($parts) !== 2) {
            return;
        }
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if ($key === '' || $value === '') {
            return;
        }
        $this->mapKeyType = new Type($key, $this->context);
        $this->mapValueType = new Type($value, $this->context);
    }

    /**
     * Split a string on $sep, ignoring separators inside angle brackets.
     *
     * @return string[]
     */
    private function splitTopLevel(string $str, string $sep): array
    {
        $depth = 0;
        $parts = [];
        $current = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $ch = $str[$i];
            if ($ch === '<') {
                $depth++;
            } elseif ($ch === '>') {
                $depth--;
            } elseif ($ch === $sep && $depth === 0) {
                $parts[] = $current;
                $current = '';
                continue;
            }
            $current .= $ch;
        }
        $parts[] = $current;
        return $parts;
    }
}
