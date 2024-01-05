<?php


namespace dokuwiki\Remote;

use dokuwiki\Remote\OpenApiDoc\DocBlockClass;
use dokuwiki\Remote\OpenApiDoc\Type;
use dokuwiki\Utf8\PhpString;

class OpenAPIGenerator
{

    protected $api;

    protected $documentation = [];

    public function __construct()
    {
        $this->api = new Api();

        $this->documentation['openapi'] = '3.1.0';
        $this->documentation['info'] = [
            'title' => 'DokuWiki API',
            'description' => 'The DokuWiki API OpenAPI specification',
            'version' => ((string)ApiCore::API_VERSION),
        ];

    }

    public function generate()
    {
        $this->addServers();
        $this->addSecurity();
        $this->addMethods();

        return json_encode($this->documentation, JSON_PRETTY_PRINT);
    }

    protected function addServers()
    {
        $this->documentation['servers'] = [
            [
                'url' => DOKU_URL . 'lib/exe/jsonrpc.php',
            ],
        ];
    }

    protected function addSecurity()
    {
        $this->documentation['components']['securitySchemes'] = [
            'basicAuth' => [
                'type' => 'http',
                'scheme' => 'basic',
            ],
            'jwt' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
            ]
        ];
        $this->documentation['security'] = [
            [
                'basicAuth' => [],
            ],
            [
                'jwt' => [],
            ],
        ];
    }

    protected function addMethods()
    {
        $methods = $this->api->getMethods();

        $this->documentation['paths'] = [];
        foreach ($methods as $method => $call) {
            $this->documentation['paths']['/' . $method] = [
                'post' => $this->getMethodDefinition($method, $call),
            ];
        }
    }

    protected function getMethodDefinition(string $method, ApiCall $call)
    {
        $description = $call->getDescription();
        $links = $call->getDocs()->getTag('link');
        if ($links) {
            $description .= "\n\n**See also:**";
            foreach ($links as $link) {
                $description .= "\n\n* " . $this->generateLink($link);
            }
        }

        $retType = $call->getReturn()['type'];
        $result = array_merge(
            [
                'description' => $call->getReturn()['description'],
                'examples' => [$this->generateExample('result', $retType->getOpenApiType())],
            ],
            $this->typeToSchema($retType)
        );

        $definition = [
            'operationId' => $method,
            'summary' => $call->getSummary(),
            'description' => $description,
            'tags' => [PhpString::ucwords($call->getCategory())],
            'requestBody' => [
                'required' => true,
                'content' => [
                    'application/json' => $this->getMethodArguments($call->getArgs()),
                ]
            ],
            'responses' => [
                200 => [
                    'description' => 'Result',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'result' => $result,
                                    'error' => [
                                        'type' => 'object',
                                        'description' => 'Error object in case of an error',
                                        'properties' => [
                                            'code' => [
                                                'type' => 'integer',
                                                'description' => 'The error code',
                                                'examples' => [0],
                                            ],
                                            'message' => [
                                                'type' => 'string',
                                                'description' => 'The error message',
                                                'examples' => ['Success'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        if ($call->isPublic()) {
            $definition['security'] = [
                new \stdClass(),
            ];
            $definition['description'] = 'This method is public and does not require authentication. ' .
                "\n\n" . $definition['description'];
        }

        if ($call->getDocs()->getTag('deprecated')) {
            $definition['deprecated'] = true;
            $definition['description'] = '**This method is deprecated.** ' . $call->getDocs()->getTag('deprecated')[0] .
                "\n\n" . $definition['description'];
        }

        return $definition;
    }

    protected function getMethodArguments($args)
    {
        if (!$args) {
            // even if no arguments are needed, we need to define a body
            // this is to ensure the openapi spec knows that a application/json header is needed
            return ['schema' => ['type' => 'null']];
        }

        $props = [];
        $reqs = [];
        $schema = [
            'schema' => [
                'type' => 'object',
                'required' => &$reqs,
                'properties' => &$props
            ]
        ];

        foreach ($args as $name => $info) {
            $example = $this->generateExample($name, $info['type']->getOpenApiType());

            $description = $info['description'];
            if ($info['optional'] && isset($info['default'])) {
                $description .= ' [_default: `' . json_encode($info['default']) . '`_]';
            }

            $props[$name] = array_merge(
                [
                    'description' => $description,
                    'examples' => [$example],
                ],
                $this->typeToSchema($info['type'])
            );
            if (!$info['optional']) $reqs[] = $name;
        }


        return $schema;
    }

    protected function generateExample($name, $type)
    {
        switch ($type) {
            case 'integer':
                if ($name === 'rev') return 0;
                if ($name === 'revision') return 0;
                if ($name === 'timestamp') return time() - 60 * 24 * 30 * 2;
                return 42;
            case 'boolean':
                return true;
            case 'string':
                if ($name === 'page') return 'playground:playground';
                if ($name === 'media') return 'wiki:dokuwiki-128.png';
                return 'some-' . $name;
            case 'array':
                return ['some-' . $name, 'other-' . $name];
            default:
                return new \stdClass();
        }
    }

    /**
     * Generates a markdown link from a dokuwiki.org URL
     *
     * @param $url
     * @return mixed|string
     */
    protected function generateLink($url)
    {
        if (preg_match('/^https?:\/\/(www\.)?dokuwiki\.org\/(.+)$/', $url, $match)) {
            $name = $match[2];

            $name = str_replace(['_', '#', ':'], [' ', ' ', ' '], $name);
            $name = PhpString::ucwords($name);

            return "[$name]($url)";
        } else {
            return $url;
        }
    }


    /**
     * Generate the OpenAPI schema for the given type
     *
     * @param Type $type
     * @return array
     * @todo add example generation here
     */
    public function typeToSchema(Type $type)
    {
        $schema = [
            'type' => $type->getOpenApiType(),
        ];

        // if a sub type is known, define the items
        if ($schema['type'] === 'array' && $type->getSubType()) {
            $schema['items'] = $this->typeToSchema($type->getSubType());
        }

        // if this is an object, define the properties
        if ($schema['type'] === 'object') {
            try {
                $baseType = $type->getBaseType();
                $doc = new DocBlockClass(new \ReflectionClass($baseType));
                $schema['properties'] = [];
                foreach ($doc->getPropertyDocs() as $property => $propertyDoc) {
                    $schema['properties'][$property] = array_merge(
                        [
                            'description' => $propertyDoc->getSummary(),
                        ],
                        $this->typeToSchema($propertyDoc->getType())
                    );
                }
            } catch (\ReflectionException $e) {
                // The class is not available, so we cannot generate a schema
            }
        }

        return $schema;
    }

}
