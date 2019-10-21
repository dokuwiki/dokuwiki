<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * Implements the 640x480 replacement
 */
class Multiplyentity extends AbstractMode
{

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
    public function getSort()
    {
        return 270;
    }
}
