<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM / CommonMark emphasis via single asterisks: `*text*`.
 *
 * Emits emphasis_open / emphasis_close — the same instructions as DokuWiki's
 * Emphasis (`//`), so both syntaxes render as <em>.
 */
class GfmEmphasis extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 80;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_emphasis';
    }

    /** @inheritdoc */
    protected function getInstructionName(): string
    {
        return 'emphasis';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        // Broken down:
        //   \*                        — opening `*`
        //   (?=                       — lookahead: a valid closer must exist
        //     [^\s*]                  —   first body char: not whitespace, not `*`
        //                                 (flanking-opener rule)
        //     (?:NOT_AT_PARA_BREAK    —   possessive run of any non-`*` char
        //        [^*])*+              —     that doesn't start a paragraph break
        //     (?<![\s*])              —   last body char: not whitespace, not `*`
        //                                 (flanking-closer rule)
        //     \*                      —   closing `*`
        //   )
        // The lookahead only reaches the nearest `*` (its body is `[^*]`), so
        // it enforces CommonMark's nearest-delimiter pairing that a plain
        // closer scan cannot express. The inherited closer check additionally
        // keeps the opener from spanning an enclosing mode's closer (e.g. a
        // stray `*` inside ''glob/*.conf'').
        //
        // The body run is possessive and the flanking-closer rule is a
        // lookbehind rather than a trailing character the run must give back:
        // `[^*]` cannot match `*`, so the run always stops at the nearest `*`
        // (or a paragraph break) and never needs to backtrack. A plain
        // quantifier with a trailing `[^\s*]` forced backtracking on every
        // opener, so a long run of `*` with no valid closer made the non-JIT
        // PCRE engine retain one backtracking frame per byte.
        return '\*(?=[^\s*](?:' . self::NOT_AT_PARA_BREAK . '[^*])*+(?<![\s*])\*)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])\*';
    }
}
