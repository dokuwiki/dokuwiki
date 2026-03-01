<?php

namespace dokuwiki\Remote\OpenApiDoc;

use Reflector;

class DocBlock
{
    /** @var Reflector The reflected object */
    protected $reflector;

    /** @var string The first line of the decription */
    protected $summary = '';

    /** @var string The description */
    protected $description = '';

    /** @var string The parsed tags */
    protected $tags = [];

    /**
     * Parse the given docblock
     *
     * The docblock can be of a method, class or property.
     *
     * @param Reflector $reflector
     */
    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
        $docblock = $reflector->getDocComment();

        // strip asterisks and leading spaces
        $docblock = trim(preg_replace(
            ['/^[ \t]*\/\*+[ \t]*/m', '/[ \t]*\*+[ \t]*/m', '/\*+\/\s*$/m', '/\s*\/\s*$/m'],
            ['', '', '', ''],
            $docblock
        ));

        // get all tags
        $tags = [];
        if (preg_match_all('/^@(\w+)\s+(.*)$/m', $docblock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tags[$match[1]][] = trim($match[2]);
            }
        }

        // strip the tags from the docblock
        $docblock = preg_replace('/^@(\w+)\s+(.*)$/m', '', $docblock);

        // what remains is summary and description
        [$summary, $description] = sexplode("\n\n", $docblock, 2, '');

        // store everything
        $this->summary = trim($summary);
        $this->description = trim($description);
        $this->tags = $tags;
    }

    /**
     * The class name of the declaring class
     *
     * @return string
     */
    protected function getContext()
    {
        return $this->reflector->getDeclaringClass()->getName();
    }

    /**
     * Get the first line of the description
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Get the full description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get all tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get a specific tag
     *
     * @param string $tag
     * @return array
     */
    public function getTag($tag)
    {
        if (!isset($this->tags[$tag])) return [];
        return $this->tags[$tag];
    }
}
