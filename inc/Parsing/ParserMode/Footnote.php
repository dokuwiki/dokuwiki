<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;

class Footnote extends AbstractMode
{
    /**
     * Footnote constructor.
     */
    public function __construct()
    {
        $this->allowedModes = ModeRegistry::getInstance()->getModesForCategories([
            ModeRegistry::CATEGORY_CONTAINER,
            ModeRegistry::CATEGORY_FORMATTING,
            ModeRegistry::CATEGORY_SUBSTITION,
            ModeRegistry::CATEGORY_PROTECTED,
            ModeRegistry::CATEGORY_DISABLED,
        ]);

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
