<?php

namespace dokuwiki\Remote\OpenApiDoc;

class DocBlockProperty extends DocBlock
{
    /** @var Type */
    protected $type;

    /**
     * Parse the given docblock
     *
     * The docblock can be of a method, class or property.
     *
     * @param \ReflectionProperty $reflector
     */
    public function __construct(\ReflectionProperty $reflector)
    {
        parent::__construct($reflector);
        $this->refineVar();
    }

    /**
     * The Type of this property
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Parse the var tag into its components
     *
     * @return void
     */
    protected function refineVar()
    {
        $refType = $this->reflector->getType();
        $this->type = new Type($refType ? $refType->getName() : 'string', $this->getContext());


        if (!isset($this->tags['var'])) return;

        [$type, $description] = array_map('trim', sexplode(' ', $this->tags['var'][0], 2, ''));
        $this->type = new Type($type, $this->getContext());
        $this->summary = $description;
    }
}
