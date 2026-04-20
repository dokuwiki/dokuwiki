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
        //     (?:                     —   optional: more body
        //       (?:NOT_AT_PARA_BREAK  —     …any non-`*` char that doesn't
        //          [^*])*             —     start a paragraph break
        //       [^\s*]                —     last body char: not whitespace, not `*`
        //                                 (flanking-closer rule)
        //     )?                      —   `?` so single-char bodies like `*a*`
        //                                 also match
        //     \*                      —   closing `*`
        //   )
        return '\*(?=[^\s*](?:(?:' . self::NOT_AT_PARA_BREAK . '[^*])*[^\s*])?\*)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])\*';
    }
}
