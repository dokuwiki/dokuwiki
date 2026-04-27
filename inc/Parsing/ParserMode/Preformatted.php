<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\Preformatted as PreformattedHandler;

class Preformatted extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 20;
    }

    /**
     * Number of leading spaces that trigger a preformatted block.
     *
     * DokuWiki's historical value is 2 spaces; Markdown uses 4. When
     * `$conf['syntax']` is `markdown` or `md+dw` (Markdown preferred),
     * we flip to 4 so indented code blocks match GFM. A single tab is
     * always a trigger regardless of the space threshold.
     */
    protected function getIndentWidth(): int
    {
        global $conf;
        $syntax = $conf['syntax'] ?? 'dokuwiki';
        return in_array($syntax, ['markdown', 'md+dw'], true) ? 4 : 2;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $indent = str_repeat(' ', $this->getIndentWidth());

        $this->Lexer->addEntryPattern('\n' . $indent, $mode, 'preformatted');
        $this->Lexer->addEntryPattern('\n\t', $mode, 'preformatted');

        // match continuation lines inside the preformatted block
        $this->Lexer->addPattern('\n' . $indent, 'preformatted');
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
