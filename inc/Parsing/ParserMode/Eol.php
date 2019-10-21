<?php

namespace dokuwiki\Parsing\ParserMode;

class Eol extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $badModes = array('listblock','table');
        if (in_array($mode, $badModes)) {
            return;
        }
        // see FS#1652, pattern extended to swallow preceding whitespace to avoid
        // issues with lines that only contain whitespace
        $this->Lexer->addSpecialPattern('(?:^[ \t]*)?\n', $mode, 'eol');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 370;
    }
}
