<?php

namespace dokuwiki\Parsing\ParserMode;

class Rss extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern("\{\{rss>[^\}]+\}\}", $mode, 'rss');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 310;
    }
}
