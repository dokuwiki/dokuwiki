<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * Implements the 640x480 replacement
 */
class Multiplyentity extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 270;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {

        $this->Lexer->addSpecialPattern(
            '(?<=\b)(?:[1-9]|\d{2,})[xX]\d+(?=\b)',
            $mode,
            'multiplyentity'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        preg_match_all('/\d+/', $match, $matches);
        $handler->addCall('multiplyentity', [$matches[0][0], $matches[0][1]], $pos);
        return true;
    }
}
