<?php

namespace dokuwiki\Parsing\ParserMode;

class Camelcaselink extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            '\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b',
            $mode,
            'camelcaselink'
        );
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 290;
    }
}
