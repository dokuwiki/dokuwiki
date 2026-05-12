<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Handler\GfmTable as GfmTableRewriter;
use dokuwiki\Parsing\ModeRegistry;

/**
 * GFM table block.
 *
 * Architecturally mirrors DokuWiki's native Table mode: an entry/exit
 * lexer state with inline modes nested via `allowedModes`, plus a small
 * post-processing rewriter (Handler\GfmTable) that turns the flat token
 * stream into the canonical DokuWiki table call sequence.
 *
 * Cells are inline-only per spec ("Block-level elements cannot be inserted
 * in a table"). Allowed nested categories therefore mirror DW Table:
 * FORMATTING, SUBSTITUTION, PROTECTED, DISABLED.
 *
 * Entry-pattern strategy: a single zero-width lookahead asserts the table
 * shape (header line containing a pipe, followed by a delimiter row whose
 * cells are exactly `:?-+:?`). Only the leading newline is consumed; the
 * lookahead validates the rest. Non-tables — paragraphs that happen to
 * contain pipes — never enter the mode.
 *
 * The internal patterns recognise:
 *   - `\|` as a cell separator, with a `(?<!\\)` lookbehind so a backslash-
 *     prefixed pipe is left as raw input — the cell-splitting concern. The
 *     unescape (turning `\|` into a literal `|`) is handled downstream:
 *     GfmEscape consumes `\|` in normal cell text, and Handler\GfmTable's
 *     unescapePipes() applies the tables-extension rewrite inside code
 *     spans, where standard §6.1 escapes don't fire.
 *   - `\n` followed by a non-newline, non-`>` character as a row separator;
 *   - any other `\n` exits the mode (blank line, blockquote start, EOF).
 *
 * Sort 55 — one below DW Table's 60 — so that in `dw+md` and `md+dw` (where
 * both modes load) the GFM lookahead-validated entry tries first; if it
 * does not see a valid delimiter row, DW Table at sort 60 takes over for
 * `\n|` rows.
 */
class GfmTable extends AbstractMode
{
    /**
     * GFM table cells parse only inline content.
     */
    public function __construct()
    {
        $this->allowedModes = ModeRegistry::getInstance()->getModesForCategories([
            ModeRegistry::CATEGORY_FORMATTING,
            ModeRegistry::CATEGORY_SUBSTITUTION,
            ModeRegistry::CATEGORY_PROTECTED,
            ModeRegistry::CATEGORY_DISABLED,
        ]);
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 55;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        ModeRegistry::getInstance()->registerBlockEolMode('gfm_table');
    }

    /**
     * Entry pattern with lookahead-validated delimiter row.
     *
     * Consumes only `\n`; the zero-width lookahead asserts:
     *   - a header line containing at least one `|`, and
     *   - a delimiter row of `:?-+:?` cells separated by `|`.
     *
     * Without that validation, any paragraph containing a pipe would
     * trigger the table mode. With it, non-tables flow through as plain
     * paragraphs.
     *
     * @inheritdoc
     */
    public function connectTo($mode)
    {
        $delim =
            '[ \t]*\|?[ \t]*:?-+:?' .
            '(?:[ \t]*\|[ \t]*:?-+:?)*' .
            '[ \t]*\|?[ \t]*';
        $entry =
            '\n(?=' .
                '[^\n]*\|[^\n]*' .  // header line containing a pipe
                '\n' . $delim .
                '(?:\n|$)' .
            ')';
        $this->Lexer->addEntryPattern($entry, $mode, 'gfm_table');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        // Cell separator. The `(?<!\\)` lookbehind keeps `\|` from being
        // treated as a separator so backslash-escaped pipes don't split
        // cells. The unescape — turning `\|` into a literal `|` in cell
        // content — is handled downstream: GfmEscape consumes `\|` in
        // normal text, and Handler\GfmTable::unescapePipes() applies the
        // tables-extension rewrite inside code spans. We just need the
        // cells to come out the right shape. Edge: `\\|` (escaped
        // backslash, then a real separator pipe) is technically wrong
        // here — the lookbehind sees the second `\` and refuses to split
        // — but GfmEscape consumes `\\` first, leaving a clean `|` at
        // separator position.
        $this->Lexer->addPattern('(?<!\\\\)\|', 'gfm_table');
        // Row separator: a newline followed by a non-newline, non-`>` char.
        // Excluding `>` lets a blockquote terminate the table (spec 201);
        // requiring a non-newline excludes blank lines and end-of-input.
        $this->Lexer->addPattern('\n(?=[^\n>])', 'gfm_table');
        // Any other newline (blank line, blockquote start, EOF) exits.
        $this->Lexer->addExitPattern('\n', 'gfm_table');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $handler->setCallWriter(new GfmTableRewriter($handler->getCallWriter()));
                // table_start carries the body position (skip the consumed `\n`).
                $handler->addCall('gfm_table_start', [$pos + 1], $pos);
                $handler->addCall('gfm_table_row', [], $pos);
                $handler->addCall('gfm_table_cell', [], $pos);
                break;

            case DOKU_LEXER_MATCHED:
                if (str_contains($match, "\n")) {
                    // Row separator: also opens the first cell of the new row.
                    $handler->addCall('gfm_table_row', [], $pos);
                    $handler->addCall('gfm_table_cell', [], $pos);
                } else {
                    // Bare `|` — cell separator within the current row.
                    $handler->addCall('gfm_table_cell', [], $pos);
                }
                break;

            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('cdata', [$match], $pos);
                break;

            case DOKU_LEXER_EXIT:
                $handler->addCall('gfm_table_end', [], $pos);
                /** @var GfmTableRewriter $reWriter */
                $reWriter = $handler->getCallWriter();
                $handler->setCallWriter($reWriter->process());
                break;
        }
        return true;
    }
}
