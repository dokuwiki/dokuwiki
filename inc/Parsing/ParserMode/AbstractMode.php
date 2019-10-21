<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * This class and all the subclasses below are used to reduce the effort required to register
 * modes with the Lexer.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
abstract class AbstractMode implements ModeInterface
{
    /** @var \dokuwiki\Parsing\Lexer\Lexer $Lexer will be injected on loading FIXME this should be done by setter */
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
