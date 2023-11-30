<?php


namespace dokuwiki\Remote;

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
            'description' => 'The DokuWiki API',
            'version' => '1.0.0',
        ];
        $this->documentation['paths'] = [];
    }

    protected function addServers()
    {
        $this->documentation['servers'] = [
            [
                'url' => DOKU_URL . 'lib/exe/jsonrpc.php',
            ],
        ];
    }

    /**
     * Parses the description of a method
     *
     * @param string $desc
     * @return array with keys 'summary', 'desc', 'args' and 'return'
     */
    protected function parseMethodDescription($desc)
    {
        $data = [
            'summary' => '',
            'desc' => '',
            'args' => [],
            'return' => '',
        ];

        $lines = explode("\n", trim($desc));
        foreach ($lines as $line) {
            $line = trim($line);

            if ($line && $line[0] === '@') {
                // this is a doc block tag
                if (str_starts_with('@param', $line)) {
                    $parts = sexplode(' ', $line, 4); // @param type $name description
                    $data['args'][] = [ltrim($parts[1], '$'), $parts[3]]; // assumes params are in the right order
                    continue;
                }

                if (str_starts_with('@return', $line)) {
                    $parts = sexplode(' ', $line, 3); // @return type description
                    $data['return'] = $parts[2];
                    continue;
                }

                // ignore all other tags
                continue;
            }

            if (empty($data['summary'])) {
                $data['summary'] = $line;
            } else {
                $data['desc'] .= $line . "\n";
            }
        }

        $data['desc'] = trim($data['desc']);
        return $data;
    }

    protected function getMethodDefinition($method, $info)
    {
        $desc = $this->parseMethodDescription($info['doc']);

        $docs = [
            'summary' => $desc['summary'],
            'description' => $desc['desc'],
            'operationId' => $method,
        ];

        $body = $this->getMethodArguments($info['args'], $desc['args']);
        if ($body) $docs['requestBody'] = $body;

        return $docs;
    }

    public function getMethodArguments($args, $info)
    {
        if (!$args) return null;

        $docs = [
            'required' => true,
            'description' => 'The positional arguments for the method',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'array',
                        'prefixItems' => [],
                        'unevaluatedItems' => false,
                    ],
                ],
            ],
        ];

        foreach ($args as $pos => $type) {

            switch ($type) {
                case 'int':
                    $type= 'integer';
                    break;
                case 'bool':
                    $type = 'boolean';
                    break;
                case 'file':
                    $type = 'string';
                    break;

            }

            $item = [
                'type' => $type,
                'name' => 'arg' . $pos,
            ];
            if (isset($info[$pos])) {
                if (isset($info[$pos][0])) $item['name'] = $info[$pos][0];
                if (isset($info[$pos][1])) $item['description'] = $info[$pos][1];
            }

            $docs['content']['application/json']['schema']['prefixItems'][] = $item;
        }
        return $docs;
    }

    protected function addMethods()
    {
        $methods = $this->api->getMethods();

        foreach ($methods as $method => $info) {
            $this->documentation['paths']['/' . $method] = [
                'post' => $this->getMethodDefinition($method, $info),
            ];
        }
    }

    public function generate()
    {
        $this->addServers();
        $this->addMethods();

        return json_encode($this->documentation, JSON_PRETTY_PRINT);
    }

}
