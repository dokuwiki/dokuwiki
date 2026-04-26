<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\ModeRegistry;

class Base extends AbstractMode
{
    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->allowedModes = ModeRegistry::getInstance()->getModesForCategories([
            ModeRegistry::CATEGORY_CONTAINER,
            ModeRegistry::CATEGORY_BASEONLY,
            ModeRegistry::CATEGORY_PARAGRAPHS,
            ModeRegistry::CATEGORY_FORMATTING,
            ModeRegistry::CATEGORY_SUBSTITION,
            ModeRegistry::CATEGORY_PROTECTED,
            ModeRegistry::CATEGORY_DISABLED,
        ]);
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 0;
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        if ($state === DOKU_LEXER_UNMATCHED) {
            $handler->addCall('cdata', [$match], $pos);
            return true;
        }
        return false;
    }
}
