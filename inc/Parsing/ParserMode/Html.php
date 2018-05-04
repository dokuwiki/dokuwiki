<?php

namespace dokuwiki\Parsing\ParserMode;

class Html extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<html>(?=.*</html>)', $mode, 'html');
        $this->Lexer->addEntryPattern('<HTML>(?=.*</HTML>)', $mode, 'htmlblock');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</html>', 'html');
        $this->Lexer->addExitPattern('</HTML>', 'htmlblock');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 190;
    }
}
