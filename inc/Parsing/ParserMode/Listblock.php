<?php

namespace dokuwiki\Parsing\ParserMode;

class Listblock extends AbstractMode
{

    /**
     * Listblock constructor.
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
        $this->Lexer->addEntryPattern('[ \t]*\n {2,}[\-\*]', $mode, 'listblock');
        $this->Lexer->addEntryPattern('[ \t]*\n\t{1,}[\-\*]', $mode, 'listblock');

        $this->Lexer->addPattern('\n {2,}[\-\*]', 'listblock');
        $this->Lexer->addPattern('\n\t{1,}[\-\*]', 'listblock');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('\n', 'listblock');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 10;
    }
}
