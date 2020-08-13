<?php

namespace dokuwiki\Parsing\ParserMode;

class Base extends AbstractMode
{

    /**
     * Base constructor.
     */
    public function __construct()
    {
        global $PARSER_MODES;

        $this->allowedModes = array_merge(
            $PARSER_MODES['container'],
            $PARSER_MODES['baseonly'],
            $PARSER_MODES['paragraphs'],
            $PARSER_MODES['formatting'],
            $PARSER_MODES['substition'],
            $PARSER_MODES['protected'],
            $PARSER_MODES['disabled']
        );
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 0;
    }
}
