<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Unformatted extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 170;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<nowiki>(?=.*</nowiki>)', $mode, 'unformatted');
        $this->Lexer->addEntryPattern('%%(?=.*%%)', $mode, 'unformattedalt');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</nowiki>', 'unformatted');
        $this->Lexer->addExitPattern('%%', 'unformattedalt');
        $this->Lexer->mapHandler('unformattedalt', 'unformatted');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        if ($state == DOKU_LEXER_UNMATCHED) {
            $handler->addCall('unformatted', [$match], $pos);
        }
        return true;
    }
}
