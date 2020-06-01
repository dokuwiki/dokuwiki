<?php

namespace dokuwiki\Parsing\ParserMode;

class Table extends AbstractMode
{

    /**
     * Table constructor.
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
        $this->Lexer->addEntryPattern('[\t ]*\n\^', $mode, 'table');
        $this->Lexer->addEntryPattern('[\t ]*\n\|', $mode, 'table');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addPattern('\n\^', 'table');
        $this->Lexer->addPattern('\n\|', 'table');
        $this->Lexer->addPattern('[\t ]*:::[\t ]*(?=[\|\^])', 'table');
        $this->Lexer->addPattern('[\t ]+', 'table');
        $this->Lexer->addPattern('\^', 'table');
        $this->Lexer->addPattern('\|', 'table');
        $this->Lexer->addExitPattern('\n', 'table');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 60;
    }
}
