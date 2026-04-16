<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;

class Preformatted extends AbstractMode
{
    /** @inheritdoc */
    public function connectTo($mode)
    {
        $markers = ModeRegistry::getInstance()->getLineStartMarkers();
        $lookahead = $markers ? '(?![' . implode('', $markers) . '])' : '';

        $this->Lexer->addEntryPattern('\n  ' . $lookahead, $mode, 'preformatted');
        $this->Lexer->addEntryPattern('\n\t' . $lookahead, $mode, 'preformatted');

        // How to effect a sub pattern with the Lexer!
        $this->Lexer->addPattern('\n  ', 'preformatted');
        $this->Lexer->addPattern('\n\t', 'preformatted');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('\n', 'preformatted');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 20;
    }
}
