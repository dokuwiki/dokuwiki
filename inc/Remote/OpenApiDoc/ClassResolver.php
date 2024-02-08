<?php

namespace dokuwiki\Remote\OpenApiDoc;

class ClassResolver
{
    /** @var ClassResolver */
    private static $instance;

    protected $classUses = [];
    protected $classDocs = [];

    /**
     * Get a singleton instance
     *
     * Constructor is public for testing purposes
     * @return ClassResolver
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Resolve a class name to a fully qualified class name
     *
     * Results are cached in the instance for reuse
     *
     * @param string $classalias The class name to resolve
     * @param string $context The classname in which context in which the class is used
     * @return string No guarantee that the class exists! No leading backslash!
     */
    public function resolve($classalias, $context)
    {
        if ($classalias[0] === '\\') {
            // Fully qualified class name given
            return ltrim($classalias, '\\');
        }
        $classinfo = $this->getClassUses($context);

        return $classinfo['uses'][$classalias] ?? $classinfo['ownNS'] . '\\' . $classalias;
    }

    /**
     * Resolve a class name to a fully qualified class name and return a DocBlockClass for it
     *
     * Results are cached in the instance for reuse
     *
     * @param string $classalias The class name to resolve
     * @param string $context The classname in which context in which the class is used
     * @return DocBlockClass|null
     */
    public function document($classalias, $context)
    {
        $class = $this->resolve($classalias, $context);
        if (!class_exists($class)) return null;

        if (isset($this->classDocs[$class])) {
            $reflector = new \ReflectionClass($class);
            $this->classDocs[$class] = new DocBlockClass($reflector);
        }

        return $this->classDocs[$class];
    }

    /**
     * Cached fetching of all defined class aliases
     *
     * @param string $class The class to parse
     * @return array
     */
    public function getClassUses($class)
    {
        if (!isset($this->classUses[$class])) {
            $reflector = new \ReflectionClass($class);
            $source = $this->readSource($reflector->getFileName(), $reflector->getStartLine());
            $this->classUses[$class] = [
                'ownNS' => $reflector->getNamespaceName(),
                'uses' => $this->tokenizeSource($source)
            ];
        }
        return $this->classUses[$class];
    }

    /**
     * Parse the use statements from the given source code
     *
     * This is a simplified version of the code by @jasondmoss - we do not support multiple
     * classed within one file
     *
     * @link https://gist.github.com/jasondmoss/6200807
     * @param string $source
     * @return array
     */
    private function tokenizeSource($source)
    {

        $tokens = token_get_all($source);

        $useStatements = [];
        $record = false;
        $currentUse = [
            'class' => '',
            'as' => ''
        ];

        foreach ($tokens as $token) {
            if (!is_array($token)) {
                // statement ended
                if ($record) {
                    $useStatements[] = $currentUse;
                    $record = false;
                    $currentUse = [
                        'class' => '',
                        'as' => ''
                    ];
                }
                continue;
            }
            $tokenname = token_name($token[0]);

            if ($token[0] === T_CLASS) {
                break;  // we reached the class itself, no need to parse further
            }

            if ($token[0] === T_USE) {
                $record = 'class';
                continue;
            }

            if ($token[0] === T_AS) {
                $record = 'as';
                continue;
            }

            if ($record) {
                switch ($token[0]) {
                    case T_STRING:
                    case T_NS_SEPARATOR:
                    case defined('T_NAME_QUALIFIED') ? T_NAME_QUALIFIED : -1: // PHP 7.4 compatibility
                        $currentUse[$record] .= $token[1];
                        break;
                }
            }
        }

        // Return a lookup table alias to FQCN
        $table = [];
        foreach ($useStatements as $useStatement) {
            $class = $useStatement['class'];
            $alias = $useStatement['as'] ?: substr($class, strrpos($class, '\\') + 1);
            $table[$alias] = $class;
        }

        return $table;
    }


    /**
     * Read file source up to the line where our class is defined.
     *
     * @return string
     */
    protected function readSource($file, $startline)
    {
        $file = fopen($file, 'r');
        $line = 0;
        $source = '';

        while (!feof($file)) {
            ++$line;

            if ($line >= $startline) {
                break;
            }

            $source .= fgets($file);
        }
        fclose($file);

        return $source;
    }
}
