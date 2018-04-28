<?php

namespace dokuwiki\ParserMode;

/**
 * This class and all the subclasses below are used to reduce the effort required to register
 * modes with the Lexer.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
abstract class AbstractMode implements ModeInterface
{
    /** @var \Doku_Lexer $Lexer will be injected on loading */
    public $Lexer;
    protected $allowedModes = array();

    /** @inheritdoc */
    abstract public function getSort();

    /** @inheritdoc */
    public function preConnect()
    {
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
    }

    /** @inheritdoc */
    public function postConnect()
    {
    }

    /** @inheritdoc */
    public function accepts($mode)
    {
        return in_array($mode, (array) $this->allowedModes);
    }
}
