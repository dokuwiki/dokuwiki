<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Preformatted as PreformattedHandler;
use dokuwiki\Parsing\ModeRegistry;

class Preformatted extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 20;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $markers = ModeRegistry::getInstance()->getLineStartMarkers();
        $lookahead = $markers ? '(?![' . implode('', $markers) . '])' : '';

        $this->Lexer->addEntryPattern('\n  ' . $lookahead, $mode, 'preformatted');
        $this->Lexer->addEntryPattern('\n\t' . $lookahead, $mode, 'preformatted');

        // match continuation lines inside the preformatted block
        $this->Lexer->addPattern('\n  ', 'preformatted');
        $this->Lexer->addPattern('\n\t', 'preformatted');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('\n', 'preformatted');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $handler->setCallWriter(new PreformattedHandler($handler->getCallWriter()));
                $handler->addCall('preformatted_start', [], $pos);
                break;
            case DOKU_LEXER_EXIT:
                $handler->addCall('preformatted_end', [], $pos);
                /** @var PreformattedHandler $reWriter */
                $reWriter = $handler->getCallWriter();
                $handler->setCallWriter($reWriter->process());
                break;
            case DOKU_LEXER_MATCHED:
                $handler->addCall('preformatted_newline', [], $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('preformatted_content', [$match], $pos);
                break;
        }

        return true;
    }
}
