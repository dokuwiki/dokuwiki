<?php

namespace dokuwiki\Parsing\Handler;

class Table extends AbstractRewriter
{

    protected $tableCalls = array();
    protected $maxCols = 0;
    protected $maxRows = 1;
    protected $currentCols = 0;
    protected $firstCell = false;
    protected $lastCellType = 'tablecell';
    protected $inTableHead = true;
    protected $currentRow = array('tableheader' => 0, 'tablecell' => 0);
    protected $countTableHeadRows = 0;

    /** @inheritdoc */
    public function finalise()
    {
        $last_call = end($this->calls);
        $this->writeCall(array('table_end',array(), $last_call[2]));

        $this->process();
        $this->callWriter->finalise();
        unset($this->callWriter);
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
                    $this->tableRowOpen(array('tablerow_open',$call[1],$call[2]));
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
        $this->tableCalls[] = array('table_open',$call[1],$call[2]);
        $this->tableCalls[] = array('tablerow_open',array(),$call[2]);
        $this->firstCell = true;
    }

    protected function tableEnd($call)
    {
        $this->tableCalls[] = array('table_close',$call[1],$call[2]);
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
            $this->currentRow = array('tablecell' => 0, 'tableheader' => 0);
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
        $this->tableCalls[] = array('tablerow_close', array(), $call[2]);

        if ($this->currentCols > $this->maxCols) {
            $this->maxCols = $this->currentCols;
        }
    }

    protected function isTableHeadRow()
    {
        $td = $this->currentRow['tablecell'];
        $th = $this->currentRow['tableheader'];

        if (!$th || $td > 2) return false;
        if (2*$td > $th) return false;

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
                $this->tableCalls[] = array('colspan',array(),$call[2]);
            }

            $this->tableCalls[] = array($this->lastCellType.'_close',array(),$call[2]);
            $this->tableCalls[] = array($call[0].'_open',array(1,null,1),$call[2]);
            $this->lastCellType = $call[0];
        } else {
            $this->tableCalls[] = array($call[0].'_open',array(1,null,1),$call[2]);
            $this->lastCellType = $call[0];
            $this->firstCell = false;
        }

        $this->currentCols++;
    }

    protected function tableDefault($call)
    {
        $this->tableCalls[] = $call;
    }

    protected function finalizeTable()
    {

        // Add the max cols and rows to the table opening
        if ($this->tableCalls[0][0] == 'table_open') {
            // Adjust to num cols not num col delimeters
            $this->tableCalls[0][1][] = $this->maxCols - 1;
            $this->tableCalls[0][1][] = $this->maxRows;
            $this->tableCalls[0][1][] = array_shift($this->tableCalls[0][1]);
        } else {
            trigger_error('First element in table call list is not table_open');
        }

        $lastRow = 0;
        $lastCell = 0;
        $cellKey = array();
        $toDelete = array();

        // if still in tableheader, then there can be no table header
        // as all rows can't be within <THEAD>
        if ($this->inTableHead) {
            $this->inTableHead = false;
            $this->countTableHeadRows = 0;
        }

        // Look for the colspan elements and increment the colspan on the
        // previous non-empty opening cell. Once done, delete all the cells
        // that contain colspans
        for ($key = 0; $key < count($this->tableCalls); ++$key) {
            $call = $this->tableCalls[$key];

            switch ($call[0]) {
                case 'table_open':
                    if ($this->countTableHeadRows) {
                        array_splice($this->tableCalls, $key+1, 0, array(
                                                          array('tablethead_open', array(), $call[2])));
                    }
                    break;

                case 'tablerow_open':
                    $lastRow++;
                    $lastCell = 0;
                    break;

                case 'tablecell_open':
                case 'tableheader_open':
                    $lastCell++;
                    $cellKey[$lastRow][$lastCell] = $key;
                    break;

                case 'table_align':
                    $prev = in_array($this->tableCalls[$key-1][0], array('tablecell_open', 'tableheader_open'));
                    $next = in_array($this->tableCalls[$key+1][0], array('tablecell_close', 'tableheader_close'));
                    // If the cell is empty, align left
                    if ($prev && $next) {
                        $this->tableCalls[$key-1][1][1] = 'left';

                        // If the previous element was a cell open, align right
                    } elseif ($prev) {
                        $this->tableCalls[$key-1][1][1] = 'right';

                        // If the next element is the close of an element, align either center or left
                    } elseif ($next) {
                        if ($this->tableCalls[$cellKey[$lastRow][$lastCell]][1][1] == 'right') {
                            $this->tableCalls[$cellKey[$lastRow][$lastCell]][1][1] = 'center';
                        } else {
                            $this->tableCalls[$cellKey[$lastRow][$lastCell]][1][1] = 'left';
                        }
                    }

                    // Now convert the whitespace back to cdata
                    $this->tableCalls[$key][0] = 'cdata';
                    break;

                case 'colspan':
                    $this->tableCalls[$key-1][1][0] = false;

                    for ($i = $key-2; $i >= $cellKey[$lastRow][1]; $i--) {
                        if ($this->tableCalls[$i][0] == 'tablecell_open' ||
                            $this->tableCalls[$i][0] == 'tableheader_open'
                        ) {
                            if (false !== $this->tableCalls[$i][1][0]) {
                                $this->tableCalls[$i][1][0]++;
                                break;
                            }
                        }
                    }

                    $toDelete[] = $key-1;
                    $toDelete[] = $key;
                    $toDelete[] = $key+1;
                    break;

                case 'rowspan':
                    if ($this->tableCalls[$key-1][0] == 'cdata') {
                        // ignore rowspan if previous call was cdata (text mixed with :::)
                        // we don't have to check next call as that wont match regex
                        $this->tableCalls[$key][0] = 'cdata';
                    } else {
                        $spanning_cell = null;

                        // can't cross thead/tbody boundary
                        if (!$this->countTableHeadRows || ($lastRow-1 != $this->countTableHeadRows)) {
                            for ($i = $lastRow-1; $i > 0; $i--) {
                                if ($this->tableCalls[$cellKey[$i][$lastCell]][0] == 'tablecell_open' ||
                                    $this->tableCalls[$cellKey[$i][$lastCell]][0] == 'tableheader_open'
                                ) {
                                    if ($this->tableCalls[$cellKey[$i][$lastCell]][1][2] >= $lastRow - $i) {
                                        $spanning_cell = $i;
                                        break;
                                    }
                                }
                            }
                        }
                        if (is_null($spanning_cell)) {
                            // No spanning cell found, so convert this cell to
                            // an empty one to avoid broken tables
                            $this->tableCalls[$key][0] = 'cdata';
                            $this->tableCalls[$key][1][0] = '';
                            break;
                        }
                        $this->tableCalls[$cellKey[$spanning_cell][$lastCell]][1][2]++;

                        $this->tableCalls[$key-1][1][2] = false;

                        $toDelete[] = $key-1;
                        $toDelete[] = $key;
                        $toDelete[] = $key+1;
                    }
                    break;

                case 'tablerow_close':
                    // Fix broken tables by adding missing cells
                    $moreCalls = array();
                    while (++$lastCell < $this->maxCols) {
                        $moreCalls[] = array('tablecell_open', array(1, null, 1), $call[2]);
                        $moreCalls[] = array('cdata', array(''), $call[2]);
                        $moreCalls[] = array('tablecell_close', array(), $call[2]);
                    }
                    $moreCallsLength = count($moreCalls);
                    if ($moreCallsLength) {
                        array_splice($this->tableCalls, $key, 0, $moreCalls);
                        $key += $moreCallsLength;
                    }

                    if ($this->countTableHeadRows == $lastRow) {
                        array_splice($this->tableCalls, $key+1, 0, array(
                            array('tablethead_close', array(), $call[2])));
                    }
                    break;
            }
        }

        // condense cdata
        $cnt = count($this->tableCalls);
        for ($key = 0; $key < $cnt; $key++) {
            if ($this->tableCalls[$key][0] == 'cdata') {
                $ckey = $key;
                $key++;
                while ($this->tableCalls[$key][0] == 'cdata') {
                    $this->tableCalls[$ckey][1][0] .= $this->tableCalls[$key][1][0];
                    $toDelete[] = $key;
                    $key++;
                }
                continue;
            }
        }

        foreach ($toDelete as $delete) {
            unset($this->tableCalls[$delete]);
        }
        $this->tableCalls = array_values($this->tableCalls);
    }
}
