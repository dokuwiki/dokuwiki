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
        // The leading (?<=\n) anchors the heading to the start of a line (the
        // Parser prepends a newline, so the first line matches too). It is a
        // lookbehind, so the newline stays out of the match and the reported
        // position lands on the heading's first character — keeping any blank
        // line above it in the previous section. The heading must occupy its
        // own line: only whitespace may surround the `=…=` run, so a
        // `== foo ==` sequence that follows other text mid-line stays plain
        // text. We're not picky about the closing `=`, two are enough.
        $this->Lexer->addSpecialPattern(
            '(?<=\n)[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)',
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
