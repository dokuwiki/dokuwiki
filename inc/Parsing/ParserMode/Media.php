<?php

namespace dokuwiki\Parsing\ParserMode;

class Media extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\{\{(?:[^\}]|(?:\}[^\}]))+\}\}", $mode, 'media');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 320;
    }
}
