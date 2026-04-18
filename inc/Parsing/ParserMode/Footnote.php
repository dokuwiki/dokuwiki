<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Nest;
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
    public function getSort()
    {
        return 150;
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
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                // footnotes can not be nested - however due to limitations in lexer it can't be prevented
                // we will still enter a new footnote mode, we just do nothing
                if ($handler->getStatus('footnote')) {
                    $handler->addCall('cdata', [$match], $pos);
                    break;
                }
                $handler->setStatus('footnote', true);

                $handler->setCallWriter(new Nest($handler->getCallWriter(), 'footnote_close'));
                $handler->addCall('footnote_open', [], $pos);
                break;
            case DOKU_LEXER_EXIT:
                // check whether we have already exited the footnote mode, can happen if the modes were nested
                if (!$handler->getStatus('footnote')) {
                    $handler->addCall('cdata', [$match], $pos);
                    break;
                }

                $handler->setStatus('footnote', false);
                $handler->addCall('footnote_close', [], $pos);

                /** @var Nest $reWriter */
                $reWriter = $handler->getCallWriter();
                $handler->setCallWriter($reWriter->process());
                break;
            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('cdata', [$match], $pos);
                break;
        }
        return true;
    }
}
