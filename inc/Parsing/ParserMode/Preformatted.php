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
     * DokuWiki's historical value is 2 spaces; Markdown uses 4. When the
     * active parse prefers Markdown (md or md+dw) we flip to 4 so indented
     * code blocks match GFM. A single tab is always a trigger regardless
     * of the space threshold.
     */
    protected function getIndentWidth(): int
    {
        return $this->registry->isMdPreferred() ? 4 : 2;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $indent = str_repeat(' ', $this->getIndentWidth());

        // An indented line is ambiguous with a list item, so the entry
        // patterns must not swallow a line whose indent begins a list - the
        // list has to win. In base mode sort order already gives the list mode
        // the tie, but table mode connects preformatted as a protected
        // sub-mode without connecting any list mode, so this lookahead is the
        // only thing that lets an indented list end the table there (in every
        // syntax the table exists in, including the mixed dw+md / md+dw
        // combinations). The guard tracks whichever list mode is actually
        // loaded. DokuWiki's Listblock treats a bare * or - as a marker even
        // with no following space, so the guard rejects those two characters
        // outright. Markdown's GfmListblock only starts a list when one of its
        // markers - a bullet -, *, + or a digit run ending in . or ) - is
        // followed by whitespace or the end of the line, so the guard mirrors
        // that and still lets an indented code block that merely starts with
        // such a character (e.g. a *** line) through.
        $listGuard = $this->registry->isMdPreferred()
            ? '(?!(?:[-*+]|\d{1,9}[.)])[ \t\n])'
            : '(?![\*\-])';

        $this->Lexer->addEntryPattern('\n' . $indent . $listGuard, $mode, 'preformatted');
        $this->Lexer->addEntryPattern('\n\t' . $listGuard, $mode, 'preformatted');

        // match continuation lines inside the preformatted block
        $this->Lexer->addPattern('\n' . $indent, 'preformatted');
        $this->Lexer->addPattern('\n\t', 'preformatted');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        // Two exits: a zero-width lookahead when the next line starts with
        // non-whitespace content (so the boundary \n stays in the stream
        // and downstream block-level modes like GfmHr or GfmHeader can
        // anchor on it), and a consuming \n fall-through for blank lines
        // and end-of-input. The lookahead-only branch is registered first
        // so PCRE's leftmost-first alternation prefers it whenever it
        // applies; the consuming branch handles the cases where it cannot.
        $this->Lexer->addExitPattern('(?=\n[^ \t\n])', 'preformatted');
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
