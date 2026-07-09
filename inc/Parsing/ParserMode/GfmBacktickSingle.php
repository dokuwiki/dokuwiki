<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * GFM inline code span bounded by single backticks: `text`.
 *
 * A backtick span is both monospace-formatted and verbatim: the content
 * is wrapped in monospace_open / monospace_close (the same instructions
 * as DokuWiki's doubled-single-quote pair, rendered as an HTML <code>
 * element) and the body is emitted through the unformatted handler
 * rather than plain cdata, so renderers that distinguish the two
 * (metadata, indexer, non-XHTML backends) treat it as literal.
 *
 * The entry pattern's lookahead only verifies three things: an opener,
 * at least one body character, and a valid closer. It does NOT enforce
 * non-whitespace body edges or a non-whitespace body interior. GFM's
 * edge rules are applied in handle() after the body has been extracted:
 *
 *   1. Line endings become single spaces.
 *   2. If the body both starts and ends with a space, and is not
 *      entirely whitespace, one space is stripped from each end.
 *
 * This lets the regex stay small while still producing GFM-correct
 * output for the tricky cases:
 *
 *   ` `          ->   <code> </code>     (all-whitespace body, no strip)
 *   ` a`         ->   <code> a</code>    (asymmetric edge, no strip)
 *   ` `` `       ->   <code>``</code>    (run of 2 inside body, strip)
 *
 * Runs of two or more backticks on either delimiter are rejected by
 * the length-boundary guards (?<!`)...(?!`), so this mode never steals
 * input from GfmBacktickDouble. GfmBacktickDouble extends this class
 * to reuse handle() and normalizeBody().
 *
 * No other inline parsing runs inside a span; allowedModes is empty.
 *
 * @see GfmBacktickDouble
 */
class GfmBacktickSingle extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 165;
    }

    /** The lexer state / mode name. Subclasses override for n≥2. */
    protected function getModeName(): string
    {
        return 'gfm_backtick_single';
    }

    /**
     * Delimiter: a lone backtick. The length-boundary guards
     * (?<!`)...(?!`) ensure a run of two or more backticks is never read
     * as an n=1 opener or closer.
     */
    protected function getDelimiterPattern(): string
    {
        return '(?<!`)`(?!`)';
    }

    /**
     * Span body. Admits runs of non-backticks, newlines that don't start
     * a blank line, and runs of two-or-more backticks — the latter live
     * inside the body since they cannot be valid n=1 closers.
     *
     * The alternatives start with mutually exclusive characters and all
     * quantifiers are possessive, so a scan over the body never
     * backtracks: it stops at the first lone backtick (or fails at the
     * paragraph break) without accumulating backtracking state.
     */
    protected function getBodyPattern(): string
    {
        return '(?:[^`\n]++|' . self::NOT_AT_PARA_BREAK . '\n|``++)++';
    }

    /**
     * Entry pattern: an opening delimiter whose lookahead verifies a body
     * and a closing delimiter ahead. The lookahead is self-limiting — a
     * lone backtick ahead is always a valid closer, so at most one scan
     * per paragraph can fail — which keeps it linear without the memoized
     * closer machinery of Lexer::addCloserPattern().
     */
    protected function getEntryPattern(): string
    {
        return $this->getDelimiterPattern()
            . '(?=' . $this->getBodyPattern() . $this->getDelimiterPattern() . ')';
    }

    /** Exit pattern: the same delimiter that opened the span. */
    protected function getExitPattern(): string
    {
        return $this->getDelimiterPattern();
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern(
            $this->getEntryPattern(),
            $mode,
            $this->getModeName()
        );
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern($this->getExitPattern(), $this->getModeName());
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        match ($state) {
            DOKU_LEXER_ENTER => $handler->addCall('monospace_open', [], $pos),
            DOKU_LEXER_EXIT => $handler->addCall('monospace_close', [], $pos),
            DOKU_LEXER_UNMATCHED => $handler->addCall(
                'unformatted',
                [$this->normalizeBody($match)],
                $pos
            ),
            default => true,
        };
        return true;
    }

    /**
     * GFM code-span body normalization: newlines become spaces; if both
     * ends are spaces and the body isn't entirely whitespace, strip one
     * space from each end.
     */
    protected function normalizeBody(string $body): string
    {
        $body = str_replace(["\r\n", "\r", "\n"], ' ', $body);
        if (
            strlen($body) >= 2
            && $body[0] === ' '
            && $body[-1] === ' '
            && trim($body) !== ''
        ) {
            $body = substr($body, 1, -1);
        }
        return $body;
    }
}
