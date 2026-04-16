<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;

class Table extends AbstractMode
{
    /**
     * Table constructor.
     */
    public function __construct()
    {
        $this->allowedModes = ModeRegistry::getInstance()->getModesForCategories([
            ModeRegistry::CATEGORY_FORMATTING,
            ModeRegistry::CATEGORY_SUBSTITION,
            ModeRegistry::CATEGORY_DISABLED,
            ModeRegistry::CATEGORY_PROTECTED,
        ]);
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
