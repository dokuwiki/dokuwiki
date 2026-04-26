<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Quote as QuoteHandler;
use dokuwiki\Parsing\ModeRegistry;

class Quote extends AbstractMode
{
    /**
     * Quote constructor.
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
        return 220;
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
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $handler->setCallWriter(new QuoteHandler($handler->getCallWriter()));
                $handler->addCall('quote_start', [$match], $pos);
                break;

            case DOKU_LEXER_EXIT:
                $handler->addCall('quote_end', [], $pos);
                /** @var QuoteHandler $reWriter */
                $reWriter = $handler->getCallWriter();
                $handler->setCallWriter($reWriter->process());
                break;

            case DOKU_LEXER_MATCHED:
                $handler->addCall('quote_newline', [$match], $pos);
                break;

            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('cdata', [$match], $pos);
                break;
        }

        return true;
    }
}
