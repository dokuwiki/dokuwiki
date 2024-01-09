<?php

namespace dokuwiki\Remote\OpenApiDoc;

use dokuwiki\Remote\Api;
use dokuwiki\Remote\ApiCall;
use dokuwiki\Remote\ApiCore;
use dokuwiki\Utf8\PhpString;
use ReflectionClass;
use ReflectionException;
use stdClass;

/**
 * Generates the OpenAPI documentation for the DokuWiki API
 */
class OpenAPIGenerator
{
    /** @var Api */
    protected $api;

    /** @var array Holds the documentation tree while building */
    protected $documentation = [];

    /**
     * OpenAPIGenerator constructor.
     */
    public function __construct()
    {
        $this->api = new Api();
    }

    /**
     * Generate the OpenAPI documentation
     *
     * @return string JSON encoded OpenAPI specification
     */
    public function generate()
    {
        $this->documentation = [];
        $this->documentation['openapi'] = '3.1.0';
        $this->documentation['info'] = [
            'title' => 'DokuWiki API',
            'description' => 'The DokuWiki API OpenAPI specification',
            'version' => ((string)ApiCore::API_VERSION),
            'x-locale' => 'en-US',
        ];

        $this->addServers();
        $this->addSecurity();
        $this->addMethods();

        return json_encode($this->documentation, JSON_PRETTY_PRINT);
    }

    /**
     * Read all error codes used in ApiCore.php
     *
     * This is useful for the documentation, but also for checking if the error codes are unique
     *
     * @return array
     * @todo Getting all classes/methods registered with the API and reading their error codes would be even better
     * @todo This is super crude. Using the PHP Tokenizer would be more sensible
     */
    public function getErrorCodes()
    {
        $lines = file(DOKU_INC . 'inc/Remote/ApiCore.php');

        $codes = [];
        $method = '';

        foreach ($lines as $no => $line) {
            if (preg_match('/ *function (\w+)/', $line, $match)) {
                $method = $match[1];
            }
            if (preg_match('/^ *throw new RemoteException\(\'([^\']+)\'.*?, (\d+)/', $line, $match)) {
                $codes[] = [
                    'line' => $no,
                    'exception' => 'RemoteException',
                    'method' => $method,
                    'code' => $match[2],
                    'message' => $match[1],
                ];
            }
            if (preg_match('/^ *throw new AccessDeniedException\(\'([^\']+)\'.*?, (\d+)/', $line, $match)) {
                $codes[] = [
                    'line' => $no,
                    'exception' => 'AccessDeniedException',
                    'method' => $method,
                    'code' => $match[2],
                    'message' => $match[1],
                ];
            }
        }

        usort($codes, static fn($a, $b) => $a['code'] <=> $b['code']);

        return $codes;
    }


    /**
     * Add the current DokuWiki instance as a server
     *
     * @return void
     */
    protected function addServers()
    {
        $this->documentation['servers'] = [
            [
                'url' => DOKU_URL . 'lib/exe/jsonrpc.php',
            ],
        ];
    }

    /**
     * Define the default security schemes
     *
     * @return void
     */
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

    /**
     * Add all methods available in the API to the documentation
     *
     * @return void
     */
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

    /**
     * Create the schema definition for a single API method
     *
     * @param string $method API method name
     * @param ApiCall $call The call definition
     * @return array
     */
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
            'summary' => $call->getSummary() ?: $method,
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
                new stdClass(),
            ];
            $definition['description'] = 'This method is public and does not require authentication. ' .
                "\n\n" . $definition['description'];
        }

        if ($call->getDocs()->getTag('deprecated')) {
            $definition['deprecated'] = true;
            $definition['description'] = '**This method is deprecated.** ' .
                $call->getDocs()->getTag('deprecated')[0] .
                "\n\n" . $definition['description'];
        }

        return $definition;
    }

    /**
     * Create the schema definition for the arguments of a single API method
     *
     * @param array $args The arguments of the method as returned by ApiCall::getArgs()
     * @return array
     */
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
                $description .= ' [_default: `' . json_encode($info['default'], JSON_THROW_ON_ERROR) . '`_]';
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

    /**
     * Generate an example value for the given parameter
     *
     * @param string $name The parameter's name
     * @param string $type The parameter's type
     * @return mixed
     */
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
                return new stdClass();
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
                $doc = new DocBlockClass(new ReflectionClass($baseType));
                $schema['properties'] = [];
                foreach ($doc->getPropertyDocs() as $property => $propertyDoc) {
                    $schema['properties'][$property] = array_merge(
                        [
                            'description' => $propertyDoc->getSummary(),
                        ],
                        $this->typeToSchema($propertyDoc->getType())
                    );
                }
            } catch (ReflectionException $e) {
                // The class is not available, so we cannot generate a schema
            }
        }

        return $schema;
    }
}
