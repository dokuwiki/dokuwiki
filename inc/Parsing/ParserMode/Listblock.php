<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Lists;
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
    public function getSort()
    {
        return 10;
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
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $handler->setCallWriter(new Lists($handler->getCallWriter()));
                $handler->addCall('list_open', [$match], $pos);
                break;
            case DOKU_LEXER_EXIT:
                $handler->addCall('list_close', [], $pos);
                /** @var Lists $reWriter */
                $reWriter = $handler->getCallWriter();
                $handler->setCallWriter($reWriter->process());
                break;
            case DOKU_LEXER_MATCHED:
                $handler->addCall('list_item', [$match], $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('cdata', [$match], $pos);
                break;
        }
        return true;
    }
}
