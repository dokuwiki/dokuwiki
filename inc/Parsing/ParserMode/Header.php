<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Header extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 50;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        //we're not picky about the closing ones, two are enough
        $this->Lexer->addSpecialPattern(
            '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)',
            $mode,
            'header'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        // get level and title
        $title = trim($match);
        $level = 7 - strspn($title, '=');
        if ($level < 1) $level = 1;
        $title = trim($title, '=');
        $title = trim($title);

        if ($handler->getStatus('section')) $handler->addCall('section_close', [], $pos);

        $handler->addCall('header', [$title, $level, $pos], $pos);

        $handler->addCall('section_open', [$level], $pos);
        $handler->setStatus('section', true);
        return true;
    }
}
