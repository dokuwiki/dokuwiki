<?php

namespace dokuwiki\Parsing\Handler;

/**
 * CallWriter rewriter for GFM tables.
 *
 * GfmTable's lexer state emits a flat token stream of marker calls
 * (`gfm_table_start`, `gfm_table_row`, `gfm_table_cell`, `gfm_table_end`)
 * interleaved with whatever inline modes (emphasis, code spans, links, …)
 * matched inside the cells. This rewriter:
 *
 *   1. Groups the flat stream into rows-of-cells, where each cell carries
 *      its own list of nested handler calls.
 *   2. Strips the empty leading and trailing cells that result from leading
 *      and trailing pipes (`| a | b |` → cells `["", " a ", " b ", ""]` →
 *      `[" a ", " b "]`).
 *   3. Parses the second row as the GFM delimiter row, deriving per-column
 *      alignment from `:-+:?` patterns and the column count from the cell
 *      count.
 *   4. Validates that the header row's cell count matches the delimiter's.
 *      On mismatch (spec example 203), emits the captured text back as a
 *      single cdata so the Block rewriter wraps it in a paragraph.
 *   5. Pads body rows that are short (spec 202) and truncates body rows
 *      that are long (spec 204) to the header's column count.
 *   6. Trims leading/trailing whitespace from each cell's edge cdata calls
 *      ("Spaces between pipes and cell content are trimmed").
 *   7. Emits the canonical DokuWiki table call sequence — `table_open`,
 *      `tablethead_open`, `tablerow_open`, per-column `tableheader_open`
 *      with alignment, `tablethead_close`, then (only when there are
 *      body rows — spec 205) `tabletbody_open`, per-row `tablerow_open`
 *      with `tablecell_open`, `tabletbody_close`, and finally
 *      `table_close`. No new handler instructions are introduced;
 *      `tabletbody_open` / `tabletbody_close` are part of DokuWiki's
 *      base renderer API but were never emitted before — DW Table omits
 *      `<tbody>` entirely. Activating them here is what frees the test
 *      renderer from having to track tbody state.
 *
 * Backslash-escaped pipes outside protected regions are consumed by
 * GfmEscape before the cell content reaches this rewriter. Inside
 * code spans (and any other whole-span PROTECTED capture) the `\|`
 * survives as literal text — and the GFM tables extension demands
 * that `\|` unescape to `|` even there, overriding §6.1's
 * "escapes don't work in code spans" rule. unescapePipes() applies
 * that rewrite per cell to every text-bearing call.
 */
class GfmTable extends AbstractRewriter
{
    /** @inheritdoc */
    protected function getClosingCall(): string
    {
        return 'gfm_table_end';
    }

    /** @inheritdoc */
    public function process()
    {
        ['rows' => $rows, 'startPos' => $startPos, 'endPos' => $endPos] = $this->groupRows();
        $rows = array_map($this->stripBoundaryEmpty(...), $rows);

        $alignments = array_map(
            fn($cell) => $this->parseAlign($this->cellText($cell)),
            $rows[1]
        );
        $cols = count($alignments);

        // Header / delimiter column-count mismatch is the spec-203 fallback.
        if (count($rows[0]) !== $cols) {
            $this->emitFallback($rows, $startPos);
            return $this->callWriter;
        }

        $headerRow = $this->unescapePipes($this->trimCellEdges($rows[0]));
        $bodyRows = array_map(
            fn($row) => $this->unescapePipes($this->trimCellEdges($this->padOrTruncate($row, $cols))),
            array_slice($rows, 2)
        );

        $out = $this->buildOutput($headerRow, $bodyRows, $alignments, $cols, $startPos, $endPos);
        $this->callWriter->writeCalls($out);
        return $this->callWriter;
    }

    /**
     * Walk $this->calls and bucket them into rows-of-cells-of-calls.
     *
     * @return array{rows: array<int, array<int, array<int, array>>>, startPos: int, endPos: int}
     *   `rows[r][c]` is a list of handler calls captured inside row `r`'s
     *   cell `c`. `startPos` and `endPos` carry the table's opening and
     *   closing source positions.
     */
    protected function groupRows(): array
    {
        $rows = [];
        $rowIdx = -1;
        $startPos = 0;
        $endPos = 0;

        foreach ($this->calls as $call) {
            switch ($call[0]) {
                case 'gfm_table_start':
                    $startPos = $call[1][0] ?? $call[2];
                    break;
                case 'gfm_table_end':
                    $endPos = $call[2];
                    break;
                case 'gfm_table_row':
                    $rows[] = [];
                    $rowIdx++;
                    break;
                case 'gfm_table_cell':
                    $rows[$rowIdx][] = [];
                    break;
                default:
                    if ($rowIdx >= 0 && !empty($rows[$rowIdx])) {
                        $cellIdx = count($rows[$rowIdx]) - 1;
                        $rows[$rowIdx][$cellIdx][] = $call;
                    }
                    break;
            }
        }

        return ['rows' => $rows, 'startPos' => $startPos, 'endPos' => $endPos];
    }

    /**
     * Remove leading and trailing empty cell from given row.
     *
     * Effects of leading and trailing pipes: `| a | b |` parses into four
     * cells `["", " a ", " b ", ""]`. A row with no surrounding pipes
     * (`a | b`) parses into two non-empty cells, which stay untouched.
     *
     * @param array $row a row as a list of cells; each cell is a list of
     *                   handler calls captured between separators
     * @return array the row with at most one boundary empty cell stripped
     *               from each end
     */
    protected function stripBoundaryEmpty(array $row): array
    {
        if ($row && $row[0] === []) array_shift($row);
        if ($row && end($row) === []) array_pop($row);
        return $row;
    }

    /**
     * Concatenate the original source text of every text-bearing call in a
     * cell. Used for delimiter parsing and the spec-203 fallback.
     *
     * Relies on the project-wide convention that any inline mode which
     * swallows source text records the matched string at args[0] — true
     * for `cdata`, `entity`, `unformatted`, `smiley`, `multiplyentity`,
     * plugin substitutions, etc. Open/close pairs carry empty args and
     * drop out naturally.
     *
     * Motivating case: Entity eats runs of `---` as em-dash entities, so
     * a naive cdata-only join would lose the delimiter dashes and
     * parseAlign() would refuse the column.
     *
     * Implementation: extract every call's args list, extract index 0
     * from each, implode.
     *
     * @param array $cellCalls handler calls captured inside one cell
     * @return string the concatenated source text
     */
    protected function cellText(array $cellCalls): string
    {
        return implode('', array_column(array_column($cellCalls, 1), 0));
    }

    /**
     * Decode a single delimiter cell into 'left' / 'center' / 'right' / null.
     *
     * Trusts the entry pattern's validation that the cell has the shape
     * `:?-+:?`; just checks for colons at the edges.
     *
     * @param string $cellText the joined source text of one delimiter cell
     * @return string|null 'left', 'center', 'right', or null when no
     *                     alignment marker is present
     */
    protected function parseAlign(string $cellText): ?string
    {
        $trimmed = trim($cellText);
        $left = str_starts_with($trimmed, ':');
        $right = str_ends_with($trimmed, ':');
        return match (true) {
            $left && $right => 'center',
            $right => 'right',
            $left => 'left',
            default => null,
        };
    }

    /**
     * Return a copy of the row padded with empty cells (spec 202) or
     * truncated to the header column count (spec 204).
     *
     * @param array $row a body row as a list of cells
     * @param int $cols the target column count derived from the delimiter row
     * @return array the row with exactly $cols cells
     */
    protected function padOrTruncate(array $row, int $cols): array
    {
        $count = count($row);
        if ($count < $cols) {
            return array_pad($row, $cols, []);
        }
        if ($count > $cols) {
            return array_slice($row, 0, $cols);
        }
        return $row;
    }

    /**
     * Return a copy of the row with each cell's first cdata ltrimmed,
     * its last cdata rtrimmed, and any cdata that became empty dropped.
     * Intermediate cdata are left intact so internal spaces are preserved.
     *
     * @param array $row a row as a list of cells
     * @return array the row with each cell's edge cdata trimmed
     */
    protected function trimCellEdges(array $row): array
    {
        return array_map($this->trimCell(...), $row);
    }

    /**
     * Helper for trimCellEdges: trim edge cdata of a single cell.
     *
     * @param array $cell the cell as a list of handler calls
     * @return array the cell with its first cdata ltrimmed, its last
     *               cdata rtrimmed, and any cdata that became empty
     *               dropped
     */
    protected function trimCell(array $cell): array
    {
        // get all cdata call indexes
        $cdataIdx = array_keys(array_filter($cell, fn($c) => $c[0] === 'cdata'));
        if ($cdataIdx) {
            // if any, trim the first and last one's text
            $cell[$cdataIdx[0]][1][0] = ltrim($cell[$cdataIdx[0]][1][0]);
            $cell[end($cdataIdx)][1][0] = rtrim($cell[end($cdataIdx)][1][0]);
        }
        // return all cells that are not cdate or are not empty after trimming
        return array_values(array_filter(
            $cell,
            fn($c) => $c[0] !== 'cdata' || $c[1][0] !== ''
        ));
    }

    /**
     * Apply the GFM tables-extension rule that `\|` always unescapes to
     * `|` inside table cells — including the bodies of code spans and
     * other whole-span PROTECTED captures, where standard §6.1 escape
     * rules don't fire. Walks every text-bearing call (cdata,
     * unformatted, entity, plugin substitutions, …) and str_replace's
     * the literal two-char sequence on its first arg. Other escapes
     * inside code spans are left alone — only `\|` gets the special
     * table treatment.
     *
     * In normal cell text, GfmEscape has already consumed `\|` upstream,
     * so this pass is a no-op there; its job is to catch the codespan
     * case that bypasses the lexer.
     *
     * @param array $row a row as a list of cells
     * @return array the row with `\|` rewritten to `|` in every cell
     */
    protected function unescapePipes(array $row): array
    {
        foreach ($row as &$cell) {
            foreach ($cell as &$call) {
                if (isset($call[1][0]) && is_string($call[1][0])) {
                    $call[1][0] = str_replace('\\|', '|', $call[1][0]);
                }
            }
        }
        return $row;
    }

    /**
     * Spec-203 fallback. Reconstruct a `|a|b|`-style line from each row's
     * cells via cellText() and emit the joined block as a single cdata so
     * the Block rewriter wraps it in a paragraph. Because cellText() also
     * walks `entity` / `unformatted` / etc., the source-text delimiter
     * characters survive even when an inline mode consumed them.
     *
     * @param array $rows the captured rows-of-cells-of-calls structure
     * @param int $pos the source position to attach to the emitted cdata
     */
    protected function emitFallback(array $rows, int $pos): void
    {
        $lines = [];
        foreach ($rows as $row) {
            $cellTexts = [];
            foreach ($row as $cell) {
                $cellTexts[] = $this->cellText($cell);
            }
            $lines[] = '|' . implode('|', $cellTexts) . '|';
        }
        $text = implode("\n", $lines);
        if ($text === '') return;
        $this->callWriter->writeCall(['cdata', [$text], $pos]);
    }

    /**
     * Assemble the canonical DokuWiki table-instruction sequence.
     *
     * `tabletbody_open` / `tabletbody_close` are emitted only when there
     * are body rows. Suppressing them for empty-body tables (spec 205)
     * matches the spec's "<thead> only, no <tbody>" expectation without
     * any state-tracking on the renderer side.
     *
     * @param array $headerRow trimmed header row, one cell per column
     * @param array $bodyRows trimmed body rows, each padded or truncated
     *                        to $cols
     * @param array $alignments per-column alignment from the delimiter
     *                          row; each entry is 'left' / 'center' /
     *                          'right' / null
     * @param int $cols column count derived from the delimiter row
     * @param int $startPos source position of the table's start
     * @param int $endPos source position of the table's end
     * @return array the canonical DokuWiki table call sequence ready for
     *               the outer call writer
     */
    protected function buildOutput(
        array $headerRow,
        array $bodyRows,
        array $alignments,
        int $cols,
        int $startPos,
        int $endPos
    ): array {
        $out = [];
        $out[] = ['table_open', [$cols, 1 + count($bodyRows), $startPos], $startPos];
        $out[] = ['tablethead_open', [], $startPos];
        $out[] = ['tablerow_open', [], $startPos];
        foreach ($headerRow as $i => $cell) {
            $out[] = ['tableheader_open', [1, $alignments[$i], 1], $startPos];
            foreach ($cell as $c) $out[] = $c;
            $out[] = ['tableheader_close', [], $startPos];
        }
        $out[] = ['tablerow_close', [], $startPos];
        $out[] = ['tablethead_close', [], $startPos];

        if ($bodyRows) {
            $out[] = ['tabletbody_open', [], $startPos];
            foreach ($bodyRows as $row) {
                $out[] = ['tablerow_open', [], $startPos];
                foreach ($row as $i => $cell) {
                    $out[] = ['tablecell_open', [1, $alignments[$i], 1], $startPos];
                    foreach ($cell as $c) $out[] = $c;
                    $out[] = ['tablecell_close', [], $startPos];
                }
                $out[] = ['tablerow_close', [], $startPos];
            }
            $out[] = ['tabletbody_close', [], $startPos];
        }
        $out[] = ['table_close', [$endPos], $endPos];
        return $out;
    }
}
