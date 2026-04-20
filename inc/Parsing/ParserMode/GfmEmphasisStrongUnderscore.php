<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM / CommonMark em-wrapping-strong via triple underscores: `___text___`.
 *
 * Only loaded when Markdown is the only or preferred syntax. Renders as
 * <em><strong>text</strong></em>. Only the exact 3+3 symmetric variant is
 * supported; longer and asymmetric runs require CommonMark's full
 * delimiter-pairing algorithm and are out of scope.
 *
 * Inherits `handle()` and `getSort()` from GfmEmphasisStrong since the
 * emitted instructions and sort priority are identical; only the delimiter
 * patterns and word-boundary rules differ.
 */
class GfmEmphasisStrongUnderscore extends GfmEmphasisStrong
{
    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_emphasis_strong_underscore';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        // NO_WORD_BEFORE + `(?<!_)` blocks intraword and longer-run openers.
        // `(?=[^\s_])` enforces the flanking-opener rule.
        // The closing-delimiter lookahead requires non-whitespace before `___`,
        // `(?!_)` for exactly-3 length, and NO_WORD_AFTER for word-boundary.
        return self::NO_WORD_BEFORE
            . '(?<!_)___(?=[^\s_])'
            . '(?=' . self::CONTENT_UNTIL_PARA . '[^\s]___(?!_)' . self::NO_WORD_AFTER . ')';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])___(?!_)' . self::NO_WORD_AFTER;
    }
}
