<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;

class Listblock extends AbstractMode
{
    /**
     * Listblock constructor.
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
    public function preConnect()
    {
        $registry = ModeRegistry::getInstance();
        $registry->registerBlockEolMode('listblock');
        $registry->registerLineStartMarkers('listblock', ['\\*', '\\-']);
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
