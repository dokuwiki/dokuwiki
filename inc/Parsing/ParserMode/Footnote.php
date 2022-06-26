<?php

namespace dokuwiki\Parsing\ParserMode;

class Footnote extends AbstractMode
{

    /**
     * Footnote constructor.
     */
    public function __construct()
    {
        global $PARSER_MODES;

        $this->allowedModes = array_merge(
            $PARSER_MODES['container'],
            $PARSER_MODES['formatting'],
            $PARSER_MODES['substition'],
            $PARSER_MODES['protected'],
            $PARSER_MODES['disabled']
        );

        unset($this->allowedModes[array_search('footnote', $this->allowedModes)]);
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern(
            '\x28\x28(?=.*\x29\x29)',
            $mode,
            'footnote'
        );
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern(
            '\x29\x29',
            'footnote'
        );
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 150;
    }
}
