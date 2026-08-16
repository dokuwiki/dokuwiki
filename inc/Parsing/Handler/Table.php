<?php

namespace dokuwiki\Parsing\Handler;

use dokuwiki\Parsing\Helpers\TableFinalizer;

class Table extends AbstractRewriter
{
    protected $tableCalls = [];
    protected $maxCols = 0;
    protected $maxRows = 1;
    protected $currentCols = 0;
    protected $firstCell = false;
    protected $lastCellType = 'tablecell';
    protected $inTableHead = true;
    protected $currentRow = ['tableheader' => 0, 'tablecell' => 0];
    protected $countTableHeadRows = 0;

    /** @inheritdoc */
    protected function getClosingCall(): string
    {
        return 'table_end';
    }

    /** @inheritdoc */
    public function process()
    {
        foreach ($this->calls as $call) {
            switch ($call[0]) {
                case 'table_start':
                    $this->tableStart($call);
                    break;
                case 'table_row':
                    $this->tableRowClose($call);
                    $this->tableRowOpen(['tablerow_open', $call[1], $call[2]]);
                    break;
                case 'tableheader':
                case 'tablecell':
                    $this->tableCell($call);
                    break;
                case 'table_end':
                    $this->tableRowClose($call);
                    $this->tableEnd($call);
                    break;
                default:
                    $this->tableDefault($call);
                    break;
            }
        }
        $this->callWriter->writeCalls($this->tableCalls);

        return $this->callWriter;
    }

    protected function tableStart($call)
    {
        $this->tableCalls[] = ['table_open', $call[1], $call[2]];
        $this->tableCalls[] = ['tablerow_open', [], $call[2]];
        $this->firstCell = true;
    }

    protected function tableEnd($call)
    {
        $this->tableCalls[] = ['table_close', $call[1], $call[2]];
        $this->finalizeTable();
    }

    protected function tableRowOpen($call)
    {
        $this->tableCalls[] = $call;
        $this->currentCols = 0;
        $this->firstCell = true;
        $this->lastCellType = 'tablecell';
        $this->maxRows++;
        if ($this->inTableHead) {
            $this->currentRow = ['tablecell' => 0, 'tableheader' => 0];
        }
    }

    protected function tableRowClose($call)
    {
        if ($this->inTableHead && ($this->inTableHead = $this->isTableHeadRow())) {
            $this->countTableHeadRows++;
        }
        // Strip off final cell opening and anything after it
        while ($discard = array_pop($this->tableCalls)) {
            if ($discard[0] == 'tablecell_open' || $discard[0] == 'tableheader_open') {
                break;
            }
            if (!empty($this->currentRow[$discard[0]])) {
                $this->currentRow[$discard[0]]--;
            }
        }
        $this->tableCalls[] = ['tablerow_close', [], $call[2]];

        if ($this->currentCols > $this->maxCols) {
            $this->maxCols = $this->currentCols;
        }
    }

    protected function isTableHeadRow()
    {
        $td = $this->currentRow['tablecell'];
        $th = $this->currentRow['tableheader'];

        if (!$th || $td > 2) return false;
        if (2 * $td > $th) return false;

        return true;
    }

    protected function tableCell($call)
    {
        if ($this->inTableHead) {
            $this->currentRow[$call[0]]++;
        }
        if (!$this->firstCell) {
            // Increase the span
            $lastCall = end($this->tableCalls);

            // A cell call which follows an open cell means an empty cell so span
            if ($lastCall[0] == 'tablecell_open' || $lastCall[0] == 'tableheader_open') {
                $this->tableCalls[] = ['colspan', [], $call[2]];
            }

            $this->tableCalls[] = [$this->lastCellType . '_close', [], $call[2]];
            $this->tableCalls[] = [$call[0] . '_open', [1, null, 1], $call[2]];
            $this->lastCellType = $call[0];
        } else {
            $this->tableCalls[] = [$call[0] . '_open', [1, null, 1], $call[2]];
            $this->lastCellType = $call[0];
            $this->firstCell = false;
        }

        $this->currentCols++;
    }

    protected function tableDefault($call)
    {
        $this->tableCalls[] = $call;
    }

    /**
     * Hand the collected calls over to be turned into the final instruction list
     */
    protected function finalizeTable()
    {
        // every delimiter opens a cell, but the one closing a row opens one that is discarded again
        $cols = $this->maxCols - 1;
        // a table cannot consist of head rows alone
        $headRows = $this->inTableHead ? 0 : $this->countTableHeadRows;

        $this->tableCalls = (new TableFinalizer($this->tableCalls, $cols, $this->maxRows, $headRows))->finalize();
    }
}
