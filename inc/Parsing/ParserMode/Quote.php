<?php

namespace dokuwiki\Parsing\ParserMode;

class Quote extends AbstractMode
{

    /**
     * Quote constructor.
     */
    public function __construct()
    {
        global $PARSER_MODES;

        $this->allowedModes = array_merge(
            $PARSER_MODES['formatting'],
            $PARSER_MODES['substition'],
            $PARSER_MODES['disabled'],
            $PARSER_MODES['protected']
        );
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('\n>{1,}', $mode, 'quote');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addPattern('\n>{1,}', 'quote');
        $this->Lexer->addExitPattern('\n', 'quote');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 220;
    }
}
