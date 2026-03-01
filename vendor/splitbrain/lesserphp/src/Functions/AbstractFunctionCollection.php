<?php

namespace LesserPHP\Functions;

use LesserPHP\Lessc;

abstract class AbstractFunctionCollection
{
    protected Lessc $lessc;

    /**
     * Constructor
     */
    public function __construct(Lessc $lessc)
    {
        $this->lessc = $lessc;
    }

    /**
     * Get the functions provided by this collection
     *
     * @return array [name => callable]
     */
    abstract public function getFunctions(): array;
}
