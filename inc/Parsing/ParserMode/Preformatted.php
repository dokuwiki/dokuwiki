<?php

namespace dokuwiki\Parsing\ParserMode;

class Preformatted extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Has hard coded awareness of lists...
        $this->Lexer->addEntryPattern('\n  (?![\*\-])', $mode, 'preformatted');
        $this->Lexer->addEntryPattern('\n\t(?![\*\-])', $mode, 'preformatted');

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
