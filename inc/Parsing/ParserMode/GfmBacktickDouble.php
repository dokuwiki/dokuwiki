<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM inline code span bounded by double backticks: ``text``.
 *
 * The whole point of the double form is to let users embed literal
 * backticks in inline code. The input ``foo`bar`` renders as
 * <code>foo`bar</code> because a lone backtick in the body cannot
 * form a valid two-backtick closer. Combined with the edge-space
 * strip rule, you can embed backticks right at the boundaries: the
 * input `` `foo` `` renders as <code>`foo`</code>.
 *
 * Extends GfmBacktickSingle to inherit handle() and normalizeBody;
 * only the delimiter length and the body character class differ.
 * Sort and category match the parent so the two modes share one
 * precedence slot — the (?<!`)...(?!`) guards on both mean the n=1
 * and n=2 patterns never steal each other's input regardless of
 * registration order.
 *
 * @see GfmBacktickSingle
 */
class GfmBacktickDouble extends GfmBacktickSingle
{
    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_backtick_double';
    }

    /**
     * Entry pattern. Same shape as the parent but with doubled
     * delimiters. The body admits a lone backtick (one that isn't
     * followed by another, so not part of a run-of-two) instead of the
     * parent's backtick runs — such a stray backtick cannot form a valid
     * n=2 closer. Deterministic and possessive like the parent's body.
     */
    protected function getEntryPattern(): string
    {
        return '(?<!`)``(?!`)(?='
            . '(?:[^`\n]++|\n(?![ \t]*\n)|`(?!`))++'
            . '(?<!`)``(?!`)'
            . ')';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<!`)``(?!`)';
    }
}
