<?php

namespace dokuwiki\Remote;


class ApiCall
{
    /** @var callable The method to be called for this endpoint */
    protected $method;

    /** @var bool Whether this call can be called without authentication */
    protected bool $isPublic = false;

    /** @var array Metadata on the accepted parameters */
    protected array $args = [];

    /** @var array Metadata on the return value */
    protected array $return = [
        'type' => 'string',
        'description' => '',
    ];

    /** @var string The summary of the method */
    protected string $summary = '';

    /** @var string The description of the method */
    protected string $description = '';

    /**
     * Make the given method available as an API call
     *
     * @param string|array $method Either [object,'method'] or 'function'
     * @throws \ReflectionException
     */
    public function __construct($method)
    {
        if (!is_callable($method)) {
            throw new \InvalidArgumentException('Method is not callable');
        }

        $this->method = $method;
        $this->parseData();
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
        return $this->args;
    }

    /**
     * Limit the arguments to the given ones
     *
     * @param string[] $args
     * @return $this
     */
    public function limitArgs($args): self
    {
        foreach ($args as $arg) {
            if (!isset($this->args[$arg])) {
                throw new \InvalidArgumentException("Unknown argument $arg");
            }
        }
        $this->args = array_intersect_key($this->args, array_flip($args));

        return $this;
    }

    /**
     * Set the description for an argument
     *
     * @param string $arg
     * @param string $description
     * @return $this
     */
    public function setArgDescription(string $arg, string $description): self
    {
        if (!isset($this->args[$arg])) {
            throw new \InvalidArgumentException('Unknown argument');
        }
        $this->args[$arg]['description'] = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getReturn(): array
    {
        return $this->return;
    }

    /**
     * Set the description for the return value
     *
     * @param string $description
     * @return $this
     */
    public function setReturnDescription(string $description): self
    {
        $this->return['description'] = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     * @return $this
     */
    public function setSummary(string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Fill in the metadata
     *
     * This uses Reflection to inspect the method signature and doc block
     *
     * @throws \ReflectionException
     */
    protected function parseData()
    {
        if (is_array($this->method)) {
            $reflect = new \ReflectionMethod($this->method[0], $this->method[1]);
        } else {
            $reflect = new \ReflectionFunction($this->method);
        }

        $docInfo = $this->parseDocBlock($reflect->getDocComment());
        $this->summary = $docInfo['summary'];
        $this->description = $docInfo['description'];

        foreach ($reflect->getParameters() as $parameter) {
            $name = $parameter->name;
            $realType = $parameter->getType();
            if ($realType) {
                $type = $realType->getName();
            } elseif (isset($docInfo['args'][$name]['type'])) {
                $type = $docInfo['args'][$name]['type'];
            } else {
                $type = 'string';
            }

            if (isset($docInfo['args'][$name]['description'])) {
                $description = $docInfo['args'][$name]['description'];
            } else {
                $description = '';
            }

            $this->args[$name] = [
                'type' => $type,
                'description' => trim($description),
            ];
        }

        $returnType = $reflect->getReturnType();
        if ($returnType) {
            $this->return['type'] = $returnType->getName();
        } elseif (isset($docInfo['return']['type'])) {
            $this->return['type'] = $docInfo['return']['type'];
        } else {
            $this->return['type'] = 'string';
        }

        if (isset($docInfo['return']['description'])) {
            $this->return['description'] = $docInfo['return']['description'];
        }
    }

    /**
     * Parse a doc block
     *
     * @param string $doc
     * @return array
     */
    protected function parseDocBlock($doc)
    {
        // strip asterisks and leading spaces
        $doc = preg_replace(
            ['/^[ \t]*\/\*+[ \t]*/m', '/[ \t]*\*+[ \t]*/m', '/\*+\/\s*$/m', '/\s*\/\s*$/m'],
            ['', '', '', ''],
            $doc
        );

        $doc = trim($doc);

        // get all tags
        $tags = [];
        if (preg_match_all('/^@(\w+)\s+(.*)$/m', $doc, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tags[$match[1]][] = trim($match[2]);
            }
        }
        $params = $this->extractDocTags($tags);

        // strip the tags from the doc
        $doc = preg_replace('/^@(\w+)\s+(.*)$/m', '', $doc);

        [$summary, $description] = sexplode("\n\n", $doc, 2, '');
        return array_merge(
            [
                'summary' => trim($summary),
                'description' => trim($description),
                'tags' => $tags,
            ],
            $params
        );
    }

    /**
     * Process the param and return tags
     *
     * @param array $tags
     * @return array
     */
    protected function extractDocTags(&$tags)
    {
        $result = [];

        if (isset($tags['param'])) {
            foreach ($tags['param'] as $param) {
                if (preg_match('/^(\w+)\s+\$(\w+)(\s+(.*))?$/m', $param, $m)) {
                    $result['args'][$m[2]] = [
                        'type' => $this->cleanTypeHint($m[1]),
                        'description' => trim($m[3] ?? ''),
                    ];
                }
            }
            unset($tags['param']);
        }


        if (isset($tags['return'])) {
            $return = $tags['return'][0];
            if (preg_match('/^(\w+)(\s+(.*))$/m', $return, $m)) {
                $result['return'] = [
                    'type' => $this->cleanTypeHint($m[1]),
                    'description' => trim($m[2] ?? '')
                ];
            }
            unset($tags['return']);
        }

        return $result;
    }

    /**
     * Matches the given type hint against the valid options for the remote API
     *
     * @param string $hint
     * @return string
     */
    protected function cleanTypeHint($hint)
    {
        $types = explode('|', $hint);
        foreach ($types as $t) {
            if (str_ends_with($t, '[]')) {
                return 'array';
            }
            if ($t === 'boolean' || $t === 'true' || $t === 'false') {
                return 'bool';
            }
            if (in_array($t, ['array', 'string', 'int', 'double', 'bool', 'null', 'date', 'file'])) {
                return $t;
            }
        }
        return 'string';
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

        foreach (array_keys($this->args) as $arg) {
            if (isset($params[$arg])) {
                $args[] = $params[$arg];
            } else {
                $args[] = null;
            }
        }

        return $args;
    }

}
