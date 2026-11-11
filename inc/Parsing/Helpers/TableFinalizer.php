<?php

namespace dokuwiki\Parsing\Helpers;

/**
 * Turns the calls collected for a single table into the final instruction list.
 *
 * The calls handed in describe the rows and cells as the parser found them.
 * This class adds the head and body sections, resolves cell alignment, expands
 * colspans and rowspans, pads short rows with empty cells and merges
 * neighbouring text calls. It walks the call list once and removes the calls it
 * marked on the way when that walk is done.
 */
class TableFinalizer
{
    /** @var int position of the colspan in the arguments of a cell opening */
    protected const CELL_COLSPAN = 0;

    /** @var int position of the alignment in the arguments of a cell opening */
    protected const CELL_ALIGN = 1;

    /** @var int position of the rowspan in the arguments of a cell opening */
    protected const CELL_ROWSPAN = 2;

    /** @var array[] the call list being rewritten */
    protected array $calls;

    /** @var int number of columns of the widest row */
    protected int $maxCols;

    /** @var int number of rows */
    protected int $maxRows;

    /** @var int number of leading rows that form the table head */
    protected int $headRows;

    /** @var array index of every cell opening call in $calls, keyed by row and column number */
    protected array $cellKeys = [];

    /** @var int[] indexes of calls in $calls to remove when the walk is done */
    protected array $toDelete = [];

    /** @var int|null index of the first call of the text run currently being merged */
    protected ?int $cdataRun = null;

    /**
     * @param array[] $calls calls collected for one table, from table_open to table_close
     * @param int $maxCols number of columns of the widest row
     * @param int $maxRows number of rows
     * @param int $headRows number of leading rows that form the table head, 0 when there is no head
     */
    public function __construct(array $calls, int $maxCols, int $maxRows, int $headRows)
    {
        $this->calls = $calls;
        $this->maxCols = $maxCols;
        $this->maxRows = $maxRows;
        $this->headRows = $headRows;
    }

    /**
     * Rewrite the calls and return the finished instruction list
     *
     * @return array[]
     * @throws \RuntimeException when the calls do not describe a whole table
     */
    public function finalize(): array
    {
        $this->assertWholeTable();
        $this->completeTableOpen();
        $this->rewriteCalls();
        $this->applyDeletions();
        $this->closeTableBody();

        return $this->calls;
    }

    /**
     * Check that the calls describe a whole table
     *
     * The rewrite relies on the opening being the first call and the closing
     * being the last, so both are checked before any of it happens.
     *
     * @throws \RuntimeException when a table call is missing from either end
     */
    protected function assertWholeTable(): void
    {
        if (($this->calls[0][0] ?? '') != 'table_open') {
            throw new \RuntimeException('First element in table call list is not table_open');
        }

        if (($this->calls[count($this->calls) - 1][0] ?? '') != 'table_close') {
            throw new \RuntimeException('Last element in table call list is not table_close');
        }
    }

    /**
     * Complete the arguments of the table opening
     *
     * The call carries the source position as its only argument. It ends up
     * with the column count, the row count and the position, in that order.
     */
    protected function completeTableOpen(): void
    {
        $this->calls[0][1][] = $this->maxCols;
        $this->calls[0][1][] = $this->maxRows;
        // move the position behind the counts
        $this->calls[0][1][] = array_shift($this->calls[0][1]);
    }

    /**
     * Walk the call list once and rewrite it
     */
    protected function rewriteCalls(): void
    {
        $row = 0;
        $cell = 0;

        $key = -1;
        while (++$key < count($this->calls)) {
            $call = $this->calls[$key];

            switch ($call[0]) {
                case 'table_open':
                    $this->openFirstSection($key, $call[2]);
                    break;

                case 'tablerow_open':
                    $row++;
                    $cell = 0;
                    break;

                case 'tablecell_open':
                case 'tableheader_open':
                    $cell++;
                    $this->cellKeys[$row][$cell] = $key;
                    break;

                case 'table_align':
                    $this->applyAlignment($key, $row, $cell);
                    break;

                case 'colspan':
                    $this->applyColspan($key, $row);
                    break;

                case 'rowspan':
                    $this->applyRowspan($key, $row, $cell);
                    break;

                case 'tablerow_close':
                    $key = $this->padRow($key, $cell, $call[2]);
                    $this->closeHeadSection($key, $row, $call[2]);
                    break;
            }

            $this->mergeCdata($key);
        }
    }

    /**
     * Open the section the first row belongs to
     *
     * That is the table head or, when the table has no header rows, the table body.
     *
     * @param int $key index of the table opening call
     * @param int $pos byte position in the original source
     */
    protected function openFirstSection(int $key, int $pos): void
    {
        $section = $this->headRows ? 'tablethead_open' : 'tabletbody_open';
        array_splice($this->calls, $key + 1, 0, [[$section, [], $pos]]);
    }

    /**
     * Set the alignment of the current cell from the whitespace around the marker
     *
     * The marker itself becomes text again.
     *
     * @param int $key index of the alignment marker call
     * @param int $row number of the current row
     * @param int $cell number of the current cell
     */
    protected function applyAlignment(int $key, int $row, int $cell): void
    {
        $openKey = $this->cellKeys[$row][$cell];
        $leading = in_array($this->calls[$key - 1][0], ['tablecell_open', 'tableheader_open']);
        $trailing = in_array($this->calls[$key + 1][0], ['tablecell_close', 'tableheader_close']);

        if ($leading && $trailing) {
            // the cell holds nothing but the whitespace
            $this->calls[$openKey][1][self::CELL_ALIGN] = 'left';
        } elseif ($leading) {
            // whitespace in front of the text pushes it to the right
            $this->calls[$openKey][1][self::CELL_ALIGN] = 'right';
        } elseif ($trailing) {
            // whitespace behind the text as well as in front of it centers it
            $centered = $this->calls[$openKey][1][self::CELL_ALIGN] == 'right';
            $this->calls[$openKey][1][self::CELL_ALIGN] = $centered ? 'center' : 'left';
        }

        // the whitespace is text again
        $this->calls[$key][0] = 'cdata';
    }

    /**
     * Widen the nearest preceding cell of the row by one column
     *
     * The marker and the empty cell holding it are marked for removal.
     *
     * @param int $key index of the colspan marker call
     * @param int $row number of the current row
     */
    protected function applyColspan(int $key, int $row): void
    {
        $this->calls[$key - 1][1][self::CELL_COLSPAN] = false;

        for ($i = $key - 2; $i >= $this->cellKeys[$row][1]; $i--) {
            if (
                $this->calls[$i][0] == 'tablecell_open' ||
                $this->calls[$i][0] == 'tableheader_open'
            ) {
                if (false !== $this->calls[$i][1][self::CELL_COLSPAN]) {
                    $this->calls[$i][1][self::CELL_COLSPAN]++;
                    break;
                }
            }
        }

        $this->toDelete[] = $key - 1;
        $this->toDelete[] = $key;
        $this->toDelete[] = $key + 1;
    }

    /**
     * Extend the matching cell of an earlier row by one row
     *
     * The marker and the empty cell holding it are marked for removal. When no
     * cell can be extended the marker becomes an empty cell instead, to avoid
     * breaking the table.
     *
     * @param int $key index of the rowspan marker call
     * @param int $row number of the current row
     * @param int $cell number of the current cell
     */
    protected function applyRowspan(int $key, int $row, int $cell): void
    {
        $spanningCell = null;

        // can't cross thead/tbody boundary
        if (!$this->headRows || ($row - 1 != $this->headRows)) {
            for ($i = $row - 1; $i > 0; $i--) {
                $above = $this->calls[$this->cellKeys[$i][$cell]];
                if ($above[0] != 'tablecell_open' && $above[0] != 'tableheader_open') continue;

                if ($above[1][self::CELL_ROWSPAN] >= $row - $i) {
                    $spanningCell = $i;
                    break;
                }
            }
        }

        if (is_null($spanningCell)) {
            $this->calls[$key][0] = 'cdata';
            $this->calls[$key][1][0] = '';
            return;
        }

        $this->calls[$this->cellKeys[$spanningCell][$cell]][1][self::CELL_ROWSPAN]++;
        $this->calls[$key - 1][1][self::CELL_ROWSPAN] = false;

        $this->toDelete[] = $key - 1;
        $this->toDelete[] = $key;
        $this->toDelete[] = $key + 1;
    }

    /**
     * Append empty cells until the row is as wide as the widest row of the table
     *
     * @param int $key index of the row closing call
     * @param int $cell number of cells the row has
     * @param int $pos byte position in the original source
     * @return int index of the row closing call after the cells were inserted
     */
    protected function padRow(int $key, int $cell, int $pos): int
    {
        $moreCalls = [];
        while ($cell < $this->maxCols) {
            $moreCalls[] = ['tablecell_open', [1, null, 1], $pos];
            $moreCalls[] = ['cdata', [''], $pos];
            $moreCalls[] = ['tablecell_close', [], $pos];
            $cell++;
        }

        array_splice($this->calls, $key, 0, $moreCalls);
        return $key + count($moreCalls);
    }

    /**
     * Close the table head after its last row and open the table body
     *
     * @param int $key index of the row closing call
     * @param int $row number of the current row
     * @param int $pos byte position in the original source
     */
    protected function closeHeadSection(int $key, int $row, int $pos): void
    {
        if ($this->headRows != $row) return;

        array_splice($this->calls, $key + 1, 0, [
            ['tablethead_close', [], $pos],
            ['tabletbody_open', [], $pos]
        ]);
    }

    /**
     * Merge the call into the run of text calls it continues
     *
     * Text calls only end up next to each other while the walk runs, because
     * alignment markers and unusable rowspan markers become text. Merging into
     * the call in front works because that call has already been visited and is
     * final, while the call behind has not.
     *
     * @param int $key index of the current call
     */
    protected function mergeCdata(int $key): void
    {
        if ($this->calls[$key][0] != 'cdata') {
            $this->cdataRun = null;
            return;
        }

        if ($this->cdataRun === null) {
            $this->cdataRun = $key;
            return;
        }

        $this->calls[$this->cdataRun][1][0] .= $this->calls[$key][1][0];
        $this->toDelete[] = $key;
    }

    /**
     * Remove the calls marked during the walk and renumber the list
     */
    protected function applyDeletions(): void
    {
        foreach ($this->toDelete as $delete) {
            unset($this->calls[$delete]);
        }
        $this->calls = array_values($this->calls);
    }

    /**
     * Close the table body in front of the table closing
     */
    protected function closeTableBody(): void
    {
        $last = count($this->calls) - 1;
        array_splice($this->calls, $last, 0, [['tabletbody_close', [], $this->calls[$last][2]]]);
    }
}
