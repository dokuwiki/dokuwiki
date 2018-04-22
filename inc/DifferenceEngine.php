<?php
/**
 * A PHP diff engine for phpwiki. (Taken from phpwiki-1.3.3)
 *
 * Additions by Axel Boldt for MediaWiki
 *
 * @copyright (C) 2000, 2001 Geoffrey T. Dairiki <dairiki@dairiki.org>
 * @license  You may copy this code freely under the conditions of the GPL.
 */
define('USE_ASSERTS', function_exists('assert'));

class _DiffOp {
    var $type;
    var $orig;
    var $closing;

    /**
     * @return _DiffOp
     */
    function reverse() {
        trigger_error("pure virtual", E_USER_ERROR);
    }

    function norig() {
        return $this->orig ? count($this->orig) : 0;
    }

    function nclosing() {
        return $this->closing ? count($this->closing) : 0;
    }
}

class _DiffOp_Copy extends _DiffOp {
    var $type = 'copy';

    function __construct($orig, $closing = false) {
        if (!is_array($closing))
            $closing = $orig;
        $this->orig = $orig;
        $this->closing = $closing;
    }

    function reverse() {
        return new _DiffOp_Copy($this->closing, $this->orig);
    }
}

class _DiffOp_Delete extends _DiffOp {
    var $type = 'delete';

    function __construct($lines) {
        $this->orig = $lines;
        $this->closing = false;
    }

    function reverse() {
        return new _DiffOp_Add($this->orig);
    }
}

class _DiffOp_Add extends _DiffOp {
    var $type = 'add';

    function __construct($lines) {
        $this->closing = $lines;
        $this->orig = false;
    }

    function reverse() {
        return new _DiffOp_Delete($this->closing);
    }
}

class _DiffOp_Change extends _DiffOp {
    var $type = 'change';

    function __construct($orig, $closing) {
        $this->orig = $orig;
        $this->closing = $closing;
    }

    function reverse() {
        return new _DiffOp_Change($this->closing, $this->orig);
    }
}


/**
 * Class used internally by Diff to actually compute the diffs.
 *
 * The algorithm used here is mostly lifted from the perl module
 * Algorithm::Diff (version 1.06) by Ned Konz, which is available at:
 *   http://www.perl.com/CPAN/authors/id/N/NE/NEDKONZ/Algorithm-Diff-1.06.zip
 *
 * More ideas are taken from:
 *   http://www.ics.uci.edu/~eppstein/161/960229.html
 *
 * Some ideas are (and a bit of code) are from from analyze.c, from GNU
 * diffutils-2.7, which can be found at:
 *   ftp://gnudist.gnu.org/pub/gnu/diffutils/diffutils-2.7.tar.gz
 *
 * closingly, some ideas (subdivision by NCHUNKS > 2, and some optimizations)
 * are my own.
 *
 * @author Geoffrey T. Dairiki
 * @access private
 */
class _DiffEngine {

    var $xchanged = array();
    var $ychanged = array();
    var $xv = array();
    var $yv = array();
    var $xind = array();
    var $yind = array();
    var $seq;
    var $in_seq;
    var $lcs;

    /**
     * @param array $from_lines
     * @param array $to_lines
     * @return _DiffOp[]
     */
    function diff($from_lines, $to_lines) {
        $n_from = count($from_lines);
        $n_to = count($to_lines);

        $this->xchanged = $this->ychanged = array();
        $this->xv = $this->yv = array();
        $this->xind = $this->yind = array();
        unset($this->seq);
        unset($this->in_seq);
        unset($this->lcs);

        // Skip leading common lines.
        for ($skip = 0; $skip < $n_from && $skip < $n_to; $skip++) {
            if ($from_lines[$skip] != $to_lines[$skip])
                break;
            $this->xchanged[$skip] = $this->ychanged[$skip] = false;
        }
        // Skip trailing common lines.
        $xi = $n_from;
        $yi = $n_to;
        for ($endskip = 0; --$xi > $skip && --$yi > $skip; $endskip++) {
            if ($from_lines[$xi] != $to_lines[$yi])
                break;
            $this->xchanged[$xi] = $this->ychanged[$yi] = false;
        }

        // Ignore lines which do not exist in both files.
        for ($xi = $skip; $xi < $n_from - $endskip; $xi++)
            $xhash[$from_lines[$xi]] = 1;
        for ($yi = $skip; $yi < $n_to - $endskip; $yi++) {
            $line = $to_lines[$yi];
            if (($this->ychanged[$yi] = empty($xhash[$line])))
                continue;
            $yhash[$line] = 1;
            $this->yv[] = $line;
            $this->yind[] = $yi;
        }
        for ($xi = $skip; $xi < $n_from - $endskip; $xi++) {
            $line = $from_lines[$xi];
            if (($this->xchanged[$xi] = empty($yhash[$line])))
                continue;
            $this->xv[] = $line;
            $this->xind[] = $xi;
        }

        // Find the LCS.
        $this->_compareseq(0, count($this->xv), 0, count($this->yv));

        // Merge edits when possible
        $this->_shift_boundaries($from_lines, $this->xchanged, $this->ychanged);
        $this->_shift_boundaries($to_lines, $this->ychanged, $this->xchanged);

        // Compute the edit operations.
        $edits = array();
        $xi = $yi = 0;
        while ($xi < $n_from || $yi < $n_to) {
            USE_ASSERTS && assert($yi < $n_to || $this->xchanged[$xi]);
            USE_ASSERTS && assert($xi < $n_from || $this->ychanged[$yi]);

            // Skip matching "snake".
            $copy = array();
            while ($xi < $n_from && $yi < $n_to && !$this->xchanged[$xi] && !$this->ychanged[$yi]) {
                $copy[] = $from_lines[$xi++];
                ++$yi;
            }
            if ($copy)
                $edits[] = new _DiffOp_Copy($copy);

            // Find deletes & adds.
            $delete = array();
            while ($xi < $n_from && $this->xchanged[$xi])
                $delete[] = $from_lines[$xi++];

            $add = array();
            while ($yi < $n_to && $this->ychanged[$yi])
                $add[] = $to_lines[$yi++];

            if ($delete && $add)
                $edits[] = new _DiffOp_Change($delete, $add);
            elseif ($delete)
                $edits[] = new _DiffOp_Delete($delete);
            elseif ($add)
                $edits[] = new _DiffOp_Add($add);
        }
        return $edits;
    }


    /**
     * Divide the Largest Common Subsequence (LCS) of the sequences
     * [XOFF, XLIM) and [YOFF, YLIM) into NCHUNKS approximately equally
     * sized segments.
     *
     * Returns (LCS, PTS).  LCS is the length of the LCS. PTS is an
     * array of NCHUNKS+1 (X, Y) indexes giving the diving points between
     * sub sequences.  The first sub-sequence is contained in [X0, X1),
     * [Y0, Y1), the second in [X1, X2), [Y1, Y2) and so on.  Note
     * that (X0, Y0) == (XOFF, YOFF) and
     * (X[NCHUNKS], Y[NCHUNKS]) == (XLIM, YLIM).
     *
     * This function assumes that the first lines of the specified portions
     * of the two files do not match, and likewise that the last lines do not
     * match.  The caller must trim matching lines from the beginning and end
     * of the portions it is going to specify.
     *
     * @param integer $xoff
     * @param integer $xlim
     * @param integer $yoff
     * @param integer $ylim
     * @param integer $nchunks
     *
     * @return array
     */
    function _diag($xoff, $xlim, $yoff, $ylim, $nchunks) {
        $flip = false;

        if ($xlim - $xoff > $ylim - $yoff) {
            // Things seems faster (I'm not sure I understand why)
            // when the shortest sequence in X.
            $flip = true;
            list ($xoff, $xlim, $yoff, $ylim) = array($yoff, $ylim, $xoff, $xlim);
        }

        if ($flip)
            for ($i = $ylim - 1; $i >= $yoff; $i--)
                $ymatches[$this->xv[$i]][] = $i;
        else
            for ($i = $ylim - 1; $i >= $yoff; $i--)
                $ymatches[$this->yv[$i]][] = $i;

        $this->lcs = 0;
        $this->seq[0]= $yoff - 1;
        $this->in_seq = array();
        $ymids[0] = array();

        $numer = $xlim - $xoff + $nchunks - 1;
        $x = $xoff;
        for ($chunk = 0; $chunk < $nchunks; $chunk++) {
            if ($chunk > 0)
                for ($i = 0; $i <= $this->lcs; $i++)
                    $ymids[$i][$chunk-1] = $this->seq[$i];

            $x1 = $xoff + (int)(($numer + ($xlim-$xoff)*$chunk) / $nchunks);
            for ( ; $x < $x1; $x++) {
                $line = $flip ? $this->yv[$x] : $this->xv[$x];
                if (empty($ymatches[$line]))
                    continue;
                $matches = $ymatches[$line];
                $switch = false;
                foreach ($matches as $y) {
                    if ($switch && $y > $this->seq[$k-1]) {
                        USE_ASSERTS && assert($y < $this->seq[$k]);
                        // Optimization: this is a common case:
                        //  next match is just replacing previous match.
                        $this->in_seq[$this->seq[$k]] = false;
                        $this->seq[$k] = $y;
                        $this->in_seq[$y] = 1;
                    }
                    else if (empty($this->in_seq[$y])) {
                        $k = $this->_lcs_pos($y);
                        USE_ASSERTS && assert($k > 0);
                        $ymids[$k] = $ymids[$k-1];
                        $switch = true;
                    }
                }
            }
        }

        $seps[] = $flip ? array($yoff, $xoff) : array($xoff, $yoff);
        $ymid = $ymids[$this->lcs];
        for ($n = 0; $n < $nchunks - 1; $n++) {
            $x1 = $xoff + (int)(($numer + ($xlim - $xoff) * $n) / $nchunks);
            $y1 = $ymid[$n] + 1;
            $seps[] = $flip ? array($y1, $x1) : array($x1, $y1);
        }
        $seps[] = $flip ? array($ylim, $xlim) : array($xlim, $ylim);

        return array($this->lcs, $seps);
    }

    function _lcs_pos($ypos) {
        $end = $this->lcs;
        if ($end == 0 || $ypos > $this->seq[$end]) {
            $this->seq[++$this->lcs] = $ypos;
            $this->in_seq[$ypos] = 1;
            return $this->lcs;
        }

        $beg = 1;
        while ($beg < $end) {
            $mid = (int)(($beg + $end) / 2);
            if ($ypos > $this->seq[$mid])
                $beg = $mid + 1;
            else
                $end = $mid;
        }

        USE_ASSERTS && assert($ypos != $this->seq[$end]);

        $this->in_seq[$this->seq[$end]] = false;
        $this->seq[$end] = $ypos;
        $this->in_seq[$ypos] = 1;
        return $end;
    }

    /**
     * Find LCS of two sequences.
     *
     * The results are recorded in the vectors $this->{x,y}changed[], by
     * storing a 1 in the element for each line that is an insertion
     * or deletion (ie. is not in the LCS).
     *
     * The subsequence of file 0 is [XOFF, XLIM) and likewise for file 1.
     *
     * Note that XLIM, YLIM are exclusive bounds.
     * All line numbers are origin-0 and discarded lines are not counted.
     *
     * @param integer $xoff
     * @param integer $xlim
     * @param integer $yoff
     * @param integer $ylim
     */
    function _compareseq($xoff, $xlim, $yoff, $ylim) {
        // Slide down the bottom initial diagonal.
        while ($xoff < $xlim && $yoff < $ylim && $this->xv[$xoff] == $this->yv[$yoff]) {
            ++$xoff;
            ++$yoff;
        }

        // Slide up the top initial diagonal.
        while ($xlim > $xoff && $ylim > $yoff && $this->xv[$xlim - 1] == $this->yv[$ylim - 1]) {
            --$xlim;
            --$ylim;
        }

        if ($xoff == $xlim || $yoff == $ylim)
            $lcs = 0;
        else {
            // This is ad hoc but seems to work well.
            //$nchunks = sqrt(min($xlim - $xoff, $ylim - $yoff) / 2.5);
            //$nchunks = max(2,min(8,(int)$nchunks));
            $nchunks = min(7, $xlim - $xoff, $ylim - $yoff) + 1;
            list ($lcs, $seps)
                = $this->_diag($xoff,$xlim,$yoff, $ylim,$nchunks);
        }

        if ($lcs == 0) {
            // X and Y sequences have no common subsequence:
            // mark all changed.
            while ($yoff < $ylim)
                $this->ychanged[$this->yind[$yoff++]] = 1;
            while ($xoff < $xlim)
                $this->xchanged[$this->xind[$xoff++]] = 1;
        }
        else {
            // Use the partitions to split this problem into subproblems.
            reset($seps);
            $pt1 = $seps[0];
            while ($pt2 = next($seps)) {
                $this->_compareseq ($pt1[0], $pt2[0], $pt1[1], $pt2[1]);
                $pt1 = $pt2;
            }
        }
    }

    /**
     * Adjust inserts/deletes of identical lines to join changes
     * as much as possible.
     *
     * We do something when a run of changed lines include a
     * line at one end and has an excluded, identical line at the other.
     * We are free to choose which identical line is included.
     * `compareseq' usually chooses the one at the beginning,
     * but usually it is cleaner to consider the following identical line
     * to be the "change".
     *
     * This is extracted verbatim from analyze.c (GNU diffutils-2.7).
     *
     * @param array $lines
     * @param array $changed
     * @param array $other_changed
     */
    function _shift_boundaries($lines, &$changed, $other_changed) {
        $i = 0;
        $j = 0;

        USE_ASSERTS && assert(count($lines) == count($changed));
        $len = count($lines);
        $other_len = count($other_changed);

        while (1) {
            /*
             * Scan forwards to find beginning of another run of changes.
             * Also keep track of the corresponding point in the other file.
             *
             * Throughout this code, $i and $j are adjusted together so that
             * the first $i elements of $changed and the first $j elements
             * of $other_changed both contain the same number of zeros
             * (unchanged lines).
             * Furthermore, $j is always kept so that $j == $other_len or
             * $other_changed[$j] == false.
             */
            while ($j < $other_len && $other_changed[$j])
                $j++;

            while ($i < $len && ! $changed[$i]) {
                USE_ASSERTS && assert($j < $other_len && ! $other_changed[$j]);
                $i++;
                $j++;
                while ($j < $other_len && $other_changed[$j])
                    $j++;
            }

            if ($i == $len)
                break;

            $start = $i;

            // Find the end of this run of changes.
            while (++$i < $len && $changed[$i])
                continue;

            do {
                /*
                 * Record the length of this run of changes, so that
                 * we can later determine whether the run has grown.
                 */
                $runlength = $i - $start;

                /*
                 * Move the changed region back, so long as the
                 * previous unchanged line matches the last changed one.
                 * This merges with previous changed regions.
                 */
                while ($start > 0 && $lines[$start - 1] == $lines[$i - 1]) {
                    $changed[--$start] = 1;
                    $changed[--$i] = false;
                    while ($start > 0 && $changed[$start - 1])
                        $start--;
                    USE_ASSERTS && assert($j > 0);
                    while ($other_changed[--$j])
                        continue;
                    USE_ASSERTS && assert($j >= 0 && !$other_changed[$j]);
                }

                /*
                 * Set CORRESPONDING to the end of the changed run, at the last
                 * point where it corresponds to a changed run in the other file.
                 * CORRESPONDING == LEN means no such point has been found.
                 */
                $corresponding = $j < $other_len ? $i : $len;

                /*
                 * Move the changed region forward, so long as the
                 * first changed line matches the following unchanged one.
                 * This merges with following changed regions.
                 * Do this second, so that if there are no merges,
                 * the changed region is moved forward as far as possible.
                 */
                while ($i < $len && $lines[$start] == $lines[$i]) {
                    $changed[$start++] = false;
                    $changed[$i++] = 1;
                    while ($i < $len && $changed[$i])
                        $i++;

                    USE_ASSERTS && assert($j < $other_len && ! $other_changed[$j]);
                    $j++;
                    if ($j < $other_len && $other_changed[$j]) {
                        $corresponding = $i;
                        while ($j < $other_len && $other_changed[$j])
                            $j++;
                    }
                }
            } while ($runlength != $i - $start);

            /*
             * If possible, move the fully-merged run of changes
             * back to a corresponding run in the other file.
             */
            while ($corresponding < $i) {
                $changed[--$start] = 1;
                $changed[--$i] = 0;
                USE_ASSERTS && assert($j > 0);
                while ($other_changed[--$j])
                    continue;
                USE_ASSERTS && assert($j >= 0 && !$other_changed[$j]);
            }
        }
    }
}

/**
 * Class representing a 'diff' between two sequences of strings.
 */
class Diff {

    var $edits;

    /**
     * Constructor.
     * Computes diff between sequences of strings.
     *
     * @param array $from_lines An array of strings.
     *                          (Typically these are lines from a file.)
     * @param array $to_lines   An array of strings.
     */
    function __construct($from_lines, $to_lines) {
        $eng = new _DiffEngine;
        $this->edits = $eng->diff($from_lines, $to_lines);
        //$this->_check($from_lines, $to_lines);
    }

    /**
     * Compute reversed Diff.
     *
     * SYNOPSIS:
     *
     *  $diff = new Diff($lines1, $lines2);
     *  $rev = $diff->reverse();
     *
     * @return Diff  A Diff object representing the inverse of the
     *               original diff.
     */
    function reverse() {
        $rev = $this;
        $rev->edits = array();
        foreach ($this->edits as $edit) {
            $rev->edits[] = $edit->reverse();
        }
        return $rev;
    }

    /**
     * Check for empty diff.
     *
     * @return bool True iff two sequences were identical.
     */
    function isEmpty() {
        foreach ($this->edits as $edit) {
            if ($edit->type != 'copy')
                return false;
        }
        return true;
    }

    /**
     * Compute the length of the Longest Common Subsequence (LCS).
     *
     * This is mostly for diagnostic purposed.
     *
     * @return int The length of the LCS.
     */
    function lcs() {
        $lcs = 0;
        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $lcs += count($edit->orig);
        }
        return $lcs;
    }

    /**
     * Get the original set of lines.
     *
     * This reconstructs the $from_lines parameter passed to the
     * constructor.
     *
     * @return array The original sequence of strings.
     */
    function orig() {
        $lines = array();

        foreach ($this->edits as $edit) {
            if ($edit->orig)
                array_splice($lines, count($lines), 0, $edit->orig);
        }
        return $lines;
    }

    /**
     * Get the closing set of lines.
     *
     * This reconstructs the $to_lines parameter passed to the
     * constructor.
     *
     * @return array The sequence of strings.
     */
    function closing() {
        $lines = array();

        foreach ($this->edits as $edit) {
            if ($edit->closing)
                array_splice($lines, count($lines), 0, $edit->closing);
        }
        return $lines;
    }

    /**
     * Check a Diff for validity.
     *
     * This is here only for debugging purposes.
     *
     * @param mixed $from_lines
     * @param mixed $to_lines
     */
    function _check($from_lines, $to_lines) {
        if (serialize($from_lines) != serialize($this->orig()))
            trigger_error("Reconstructed original doesn't match", E_USER_ERROR);
        if (serialize($to_lines) != serialize($this->closing()))
            trigger_error("Reconstructed closing doesn't match", E_USER_ERROR);

        $rev = $this->reverse();
        if (serialize($to_lines) != serialize($rev->orig()))
            trigger_error("Reversed original doesn't match", E_USER_ERROR);
        if (serialize($from_lines) != serialize($rev->closing()))
            trigger_error("Reversed closing doesn't match", E_USER_ERROR);

        $prevtype = 'none';
        foreach ($this->edits as $edit) {
            if ($prevtype == $edit->type)
                trigger_error("Edit sequence is non-optimal", E_USER_ERROR);
            $prevtype = $edit->type;
        }

        $lcs = $this->lcs();
        trigger_error("Diff okay: LCS = $lcs", E_USER_NOTICE);
    }
}

/**
 * FIXME: bad name.
 */
class MappedDiff extends Diff {
    /**
     * Constructor.
     *
     * Computes diff between sequences of strings.
     *
     * This can be used to compute things like
     * case-insensitve diffs, or diffs which ignore
     * changes in white-space.
     *
     * @param string[] $from_lines         An array of strings.
     *                                     (Typically these are lines from a file.)
     *
     * @param string[] $to_lines           An array of strings.
     *
     * @param string[] $mapped_from_lines  This array should
     *                                     have the same size number of elements as $from_lines.
     *                                     The elements in $mapped_from_lines and
     *                                     $mapped_to_lines are what is actually compared
     *                                     when computing the diff.
     *
     * @param string[] $mapped_to_lines    This array should
     *                                     have the same number of elements as $to_lines.
     */
    function __construct($from_lines, $to_lines, $mapped_from_lines, $mapped_to_lines) {

        assert(count($from_lines) == count($mapped_from_lines));
        assert(count($to_lines) == count($mapped_to_lines));

        parent::__construct($mapped_from_lines, $mapped_to_lines);

        $xi = $yi = 0;
        $ecnt = count($this->edits);
        for ($i = 0; $i < $ecnt; $i++) {
            $orig = &$this->edits[$i]->orig;
            if (is_array($orig)) {
                $orig = array_slice($from_lines, $xi, count($orig));
                $xi += count($orig);
            }

            $closing = &$this->edits[$i]->closing;
            if (is_array($closing)) {
                $closing = array_slice($to_lines, $yi, count($closing));
                $yi += count($closing);
            }
        }
    }
}

/**
 * A class to format Diffs
 *
 * This class formats the diff in classic diff format.
 * It is intended that this class be customized via inheritance,
 * to obtain fancier outputs.
 */
class DiffFormatter {
    /**
     * Number of leading context "lines" to preserve.
     *
     * This should be left at zero for this class, but subclasses
     * may want to set this to other values.
     */
    var $leading_context_lines = 0;

    /**
     * Number of trailing context "lines" to preserve.
     *
     * This should be left at zero for this class, but subclasses
     * may want to set this to other values.
     */
    var $trailing_context_lines = 0;

    /**
     * Format a diff.
     *
     * @param Diff $diff A Diff object.
     * @return string The formatted output.
     */
    function format($diff) {

        $xi = $yi = 1;
        $x0 = $y0 = 0;
        $block = false;
        $context = array();

        $nlead = $this->leading_context_lines;
        $ntrail = $this->trailing_context_lines;

        $this->_start_diff();

        foreach ($diff->edits as $edit) {
            if ($edit->type == 'copy') {
                if (is_array($block)) {
                    if (count($edit->orig) <= $nlead + $ntrail) {
                        $block[] = $edit;
                    }
                    else{
                        if ($ntrail) {
                            $context = array_slice($edit->orig, 0, $ntrail);
                            $block[] = new _DiffOp_Copy($context);
                        }
                        $this->_block($x0, $ntrail + $xi - $x0, $y0, $ntrail + $yi - $y0, $block);
                        $block = false;
                    }
                }
                $context = $edit->orig;
            }
            else {
                if (! is_array($block)) {
                    $context = array_slice($context, count($context) - $nlead);
                    $x0 = $xi - count($context);
                    $y0 = $yi - count($context);
                    $block = array();
                    if ($context)
                        $block[] = new _DiffOp_Copy($context);
                }
                $block[] = $edit;
            }

            if ($edit->orig)
                $xi += count($edit->orig);
            if ($edit->closing)
                $yi += count($edit->closing);
        }

        if (is_array($block))
            $this->_block($x0, $xi - $x0, $y0, $yi - $y0, $block);

        return $this->_end_diff();
    }

    /**
     * @param int $xbeg
     * @param int $xlen
     * @param int $ybeg
     * @param int $ylen
     * @param array $edits
     */
    function _block($xbeg, $xlen, $ybeg, $ylen, &$edits) {
        $this->_start_block($this->_block_header($xbeg, $xlen, $ybeg, $ylen));
        foreach ($edits as $edit) {
            if ($edit->type == 'copy')
                $this->_context($edit->orig);
            elseif ($edit->type == 'add')
                $this->_added($edit->closing);
            elseif ($edit->type == 'delete')
                $this->_deleted($edit->orig);
            elseif ($edit->type == 'change')
                $this->_changed($edit->orig, $edit->closing);
            else
                trigger_error("Unknown edit type", E_USER_ERROR);
        }
        $this->_end_block();
    }

    function _start_diff() {
        ob_start();
    }

    function _end_diff() {
        $val = ob_get_contents();
        ob_end_clean();
        return $val;
    }

    /**
     * @param int $xbeg
     * @param int $xlen
     * @param int $ybeg
     * @param int $ylen
     * @return string
     */
    function _block_header($xbeg, $xlen, $ybeg, $ylen) {
        if ($xlen > 1)
            $xbeg .= "," . ($xbeg + $xlen - 1);
        if ($ylen > 1)
            $ybeg .= "," . ($ybeg + $ylen - 1);

        return $xbeg . ($xlen ? ($ylen ? 'c' : 'd') : 'a') . $ybeg;
    }

    /**
     * @param string $header
     */
    function _start_block($header) {
        echo $header;
    }

    function _end_block() {
    }

    function _lines($lines, $prefix = ' ') {
        foreach ($lines as $line)
            echo "$prefix ".$this->_escape($line)."\n";
    }

    function _context($lines) {
        $this->_lines($lines);
    }

    function _added($lines) {
        $this->_lines($lines, ">");
    }
    function _deleted($lines) {
        $this->_lines($lines, "<");
    }

    function _changed($orig, $closing) {
        $this->_deleted($orig);
        echo "---\n";
        $this->_added($closing);
    }

    /**
     * Escape string
     *
     * Override this method within other formatters if escaping required.
     * Base class requires $str to be returned WITHOUT escaping.
     *
     * @param $str string Text string to escape
     * @return string The escaped string.
     */
    function _escape($str){
        return $str;
    }
}

/**
 * Utilityclass for styling HTML formatted diffs
 *
 * Depends on global var $DIFF_INLINESTYLES, if true some minimal predefined
 * inline styles are used. Useful for HTML mails and RSS feeds
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class HTMLDiff {
    /**
     * Holds the style names and basic CSS
     */
    static public $styles = array(
            'diff-addedline'    => 'background-color: #ddffdd;',
            'diff-deletedline'  => 'background-color: #ffdddd;',
            'diff-context'      => 'background-color: #f5f5f5;',
            'diff-mark'         => 'color: #ff0000;',
        );

    /**
     * Return a class or style parameter
     *
     * @param string $classname
     *
     * @return string
     */
    static function css($classname){
        global $DIFF_INLINESTYLES;

        if($DIFF_INLINESTYLES){
            if(!isset(self::$styles[$classname])) return '';
            return 'style="'.self::$styles[$classname].'"';
        }else{
            return 'class="'.$classname.'"';
        }
    }
}

/**
 *  Additions by Axel Boldt follow, partly taken from diff.php, phpwiki-1.3.3
 *
 */

define('NBSP', "\xC2\xA0");     // utf-8 non-breaking space.

class _HWLDF_WordAccumulator {

    function __construct() {
        $this->_lines = array();
        $this->_line = '';
        $this->_group = '';
        $this->_tag = '';
    }

    function _flushGroup($new_tag) {
        if ($this->_group !== '') {
            if ($this->_tag == 'mark')
                $this->_line .= '<strong '.HTMLDiff::css('diff-mark').'>'.$this->_escape($this->_group).'</strong>';
            elseif ($this->_tag == 'add')
                $this->_line .= '<span '.HTMLDiff::css('diff-addedline').'>'.$this->_escape($this->_group).'</span>';
            elseif ($this->_tag == 'del')
                $this->_line .= '<span '.HTMLDiff::css('diff-deletedline').'><del>'.$this->_escape($this->_group).'</del></span>';
            else
                $this->_line .= $this->_escape($this->_group);
        }
        $this->_group = '';
        $this->_tag = $new_tag;
    }

    /**
     * @param string $new_tag
     */
    function _flushLine($new_tag) {
        $this->_flushGroup($new_tag);
        if ($this->_line != '')
            $this->_lines[] = $this->_line;
        $this->_line = '';
    }

    function addWords($words, $tag = '') {
        if ($tag != $this->_tag)
            $this->_flushGroup($tag);

        foreach ($words as $word) {
            // new-line should only come as first char of word.
            if ($word == '')
                continue;
            if ($word[0] == "\n") {
                $this->_group .= NBSP;
                $this->_flushLine($tag);
                $word = substr($word, 1);
            }
            assert(!strstr($word, "\n"));
            $this->_group .= $word;
        }
    }

    function getLines() {
        $this->_flushLine('~done');
        return $this->_lines;
    }

    function _escape($str){
        return hsc($str);
    }
}

class WordLevelDiff extends MappedDiff {

    function __construct($orig_lines, $closing_lines) {
        list ($orig_words, $orig_stripped) = $this->_split($orig_lines);
        list ($closing_words, $closing_stripped) = $this->_split($closing_lines);

        parent::__construct($orig_words, $closing_words, $orig_stripped, $closing_stripped);
    }

    function _split($lines) {
        if (!preg_match_all('/ ( [^\S\n]+ | [0-9_A-Za-z\x80-\xff]+ | . ) (?: (?!< \n) [^\S\n])? /xsu',
             implode("\n", $lines), $m)) {
            return array(array(''), array(''));
        }
        return array($m[0], $m[1]);
    }

    function orig() {
        $orig = new _HWLDF_WordAccumulator;

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $orig->addWords($edit->orig);
            elseif ($edit->orig)
                $orig->addWords($edit->orig, 'mark');
        }
        return $orig->getLines();
    }

    function closing() {
        $closing = new _HWLDF_WordAccumulator;

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $closing->addWords($edit->closing);
            elseif ($edit->closing)
                $closing->addWords($edit->closing, 'mark');
        }
        return $closing->getLines();
    }
}

class InlineWordLevelDiff extends MappedDiff {

    function __construct($orig_lines, $closing_lines) {
        list ($orig_words, $orig_stripped) = $this->_split($orig_lines);
        list ($closing_words, $closing_stripped) = $this->_split($closing_lines);

        parent::__construct($orig_words, $closing_words, $orig_stripped, $closing_stripped);
    }

    function _split($lines) {
        if (!preg_match_all('/ ( [^\S\n]+ | [0-9_A-Za-z\x80-\xff]+ | . ) (?: (?!< \n) [^\S\n])? /xsu',
             implode("\n", $lines), $m)) {
            return array(array(''), array(''));
        }
        return array($m[0], $m[1]);
    }

    function inline() {
        $orig = new _HWLDF_WordAccumulator;
        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy')
                $orig->addWords($edit->closing);
            elseif ($edit->type == 'change'){
                $orig->addWords($edit->orig, 'del');
                $orig->addWords($edit->closing, 'add');
            } elseif ($edit->type == 'delete')
                $orig->addWords($edit->orig, 'del');
            elseif ($edit->type == 'add')
                $orig->addWords($edit->closing, 'add');
            elseif ($edit->orig)
                $orig->addWords($edit->orig, 'del');
        }
        return $orig->getLines();
    }
}

/**
 * "Unified" diff formatter.
 *
 * This class formats the diff in classic "unified diff" format.
 *
 * NOTE: output is plain text and unsafe for use in HTML without escaping.
 */
class UnifiedDiffFormatter extends DiffFormatter {

    function __construct($context_lines = 4) {
        $this->leading_context_lines = $context_lines;
        $this->trailing_context_lines = $context_lines;
    }

    function _block_header($xbeg, $xlen, $ybeg, $ylen) {
        if ($xlen != 1)
            $xbeg .= "," . $xlen;
        if ($ylen != 1)
            $ybeg .= "," . $ylen;
        return "@@ -$xbeg +$ybeg @@\n";
    }

    function _added($lines) {
        $this->_lines($lines, "+");
    }
    function _deleted($lines) {
        $this->_lines($lines, "-");
    }
    function _changed($orig, $final) {
        $this->_deleted($orig);
        $this->_added($final);
    }
}

/**
 *  Wikipedia Table style diff formatter.
 *
 */
class TableDiffFormatter extends DiffFormatter {
    var $colspan = 2;

    function __construct() {
        $this->leading_context_lines = 2;
        $this->trailing_context_lines = 2;
    }

    /**
     * @param Diff $diff
     * @return string
     */
    function format($diff) {
        // Preserve whitespaces by converting some to non-breaking spaces.
        // Do not convert all of them to allow word-wrap.
        $val = parent::format($diff);
        $val = str_replace('  ','&#160; ', $val);
        $val = preg_replace('/ (?=<)|(?<=[ >]) /', '&#160;', $val);
        return $val;
    }

    function _pre($text){
        $text = htmlspecialchars($text);
        return $text;
    }

    function _block_header($xbeg, $xlen, $ybeg, $ylen) {
        global $lang;
        $l1 = $lang['line'].' '.$xbeg;
        $l2 = $lang['line'].' '.$ybeg;
        $r = '<tr><td '.HTMLDiff::css('diff-blockheader').' colspan="'.$this->colspan.'">'.$l1.":</td>\n".
             '<td '.HTMLDiff::css('diff-blockheader').' colspan="'.$this->colspan.'">'.$l2.":</td>\n".
             "</tr>\n";
        return $r;
    }

    function _start_block($header) {
        print($header);
    }

    function _end_block() {
    }

    function _lines($lines, $prefix=' ', $color="white") {
    }

    function addedLine($line,$escaped=false) {
        if (!$escaped){
            $line = $this->_escape($line);
        }
        return '<td '.HTMLDiff::css('diff-lineheader').'>+</td>'.
               '<td '.HTMLDiff::css('diff-addedline').'>' .  $line.'</td>';
    }

    function deletedLine($line,$escaped=false) {
        if (!$escaped){
            $line = $this->_escape($line);
        }
        return '<td '.HTMLDiff::css('diff-lineheader').'>-</td>'.
               '<td '.HTMLDiff::css('diff-deletedline').'>' .  $line.'</td>';
    }

    function emptyLine() {
        return '<td colspan="'.$this->colspan.'">&#160;</td>';
    }

    function contextLine($line) {
        return '<td '.HTMLDiff::css('diff-lineheader').'>&#160;</td>'.
               '<td '.HTMLDiff::css('diff-context').'>'.$this->_escape($line).'</td>';
    }

    function _added($lines) {
        $this->_addedLines($lines,false);
    }

    function _addedLines($lines,$escaped=false){
        foreach ($lines as $line) {
            print('<tr>' . $this->emptyLine() . $this->addedLine($line,$escaped) . "</tr>\n");
        }
    }

    function _deleted($lines) {
        foreach ($lines as $line) {
            print('<tr>' . $this->deletedLine($line) . $this->emptyLine() . "</tr>\n");
        }
    }

    function _context($lines) {
        foreach ($lines as $line) {
            print('<tr>' . $this->contextLine($line) .  $this->contextLine($line) . "</tr>\n");
        }
    }

    function _changed($orig, $closing) {
        $diff = new WordLevelDiff($orig, $closing);  // this escapes the diff data
        $del = $diff->orig();
        $add = $diff->closing();

        while ($line = array_shift($del)) {
            $aline = array_shift($add);
            print('<tr>' . $this->deletedLine($line,true) . $this->addedLine($aline,true) . "</tr>\n");
        }
        $this->_addedLines($add,true); # If any leftovers
    }

    function _escape($str) {
        return hsc($str);
    }
}

/**
 *  Inline style diff formatter.
 *
 */
class InlineDiffFormatter extends DiffFormatter {
    var $colspan = 2;

    function __construct() {
        $this->leading_context_lines = 2;
        $this->trailing_context_lines = 2;
    }

    /**
     * @param Diff $diff
     * @return string
     */
    function format($diff) {
        // Preserve whitespaces by converting some to non-breaking spaces.
        // Do not convert all of them to allow word-wrap.
        $val = parent::format($diff);
        $val = str_replace('  ','&#160; ', $val);
        $val = preg_replace('/ (?=<)|(?<=[ >]) /', '&#160;', $val);
        return $val;
    }

    function _pre($text){
        $text = htmlspecialchars($text);
        return $text;
    }

    function _block_header($xbeg, $xlen, $ybeg, $ylen) {
        global $lang;
        if ($xlen != 1)
            $xbeg .= "," . $xlen;
        if ($ylen != 1)
            $ybeg .= "," . $ylen;
        $r = '<tr><td colspan="'.$this->colspan.'" '.HTMLDiff::css('diff-blockheader').'>@@ '.$lang['line']." -$xbeg +$ybeg @@";
        $r .= ' <span '.HTMLDiff::css('diff-deletedline').'><del>'.$lang['deleted'].'</del></span>';
        $r .= ' <span '.HTMLDiff::css('diff-addedline').'>'.$lang['created'].'</span>';
        $r .= "</td></tr>\n";
        return $r;
    }

    function _start_block($header) {
        print($header."\n");
    }

    function _end_block() {
    }

    function _lines($lines, $prefix=' ', $color="white") {
    }

    function _added($lines) {
        foreach ($lines as $line) {
            print('<tr><td '.HTMLDiff::css('diff-lineheader').'>&#160;</td><td '.HTMLDiff::css('diff-addedline').'>'. $this->_escape($line) . "</td></tr>\n");
        }
    }

    function _deleted($lines) {
        foreach ($lines as $line) {
            print('<tr><td '.HTMLDiff::css('diff-lineheader').'>&#160;</td><td '.HTMLDiff::css('diff-deletedline').'><del>' . $this->_escape($line) . "</del></td></tr>\n");
        }
    }

    function _context($lines) {
        foreach ($lines as $line) {
            print('<tr><td '.HTMLDiff::css('diff-lineheader').'>&#160;</td><td '.HTMLDiff::css('diff-context').'>'. $this->_escape($line) ."</td></tr>\n");
        }
    }

    function _changed($orig, $closing) {
        $diff = new InlineWordLevelDiff($orig, $closing);  // this escapes the diff data
        $add = $diff->inline();

        foreach ($add as $line)
            print('<tr><td '.HTMLDiff::css('diff-lineheader').'>&#160;</td><td>'.$line."</td></tr>\n");
    }

    function _escape($str) {
        return hsc($str);
    }
}

/**
 * A class for computing three way diffs.
 *
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 */
class Diff3 extends Diff {

    /**
     * Conflict counter.
     *
     * @var integer
     */
    var $_conflictingBlocks = 0;

    /**
     * Computes diff between 3 sequences of strings.
     *
     * @param array $orig    The original lines to use.
     * @param array $final1  The first version to compare to.
     * @param array $final2  The second version to compare to.
     */
    function __construct($orig, $final1, $final2) {
        $engine = new _DiffEngine();

        $this->_edits = $this->_diff3($engine->diff($orig, $final1),
                                      $engine->diff($orig, $final2));
    }

    /**
     * Returns the merged lines
     *
     * @param string $label1  label for first version
     * @param string $label2  label for second version
     * @param string $label3  separator between versions
     * @return array          lines of the merged text
     */
    function mergedOutput($label1='<<<<<<<',$label2='>>>>>>>',$label3='=======') {
        $lines = array();
        foreach ($this->_edits as $edit) {
            if ($edit->isConflict()) {
                /* FIXME: this should probably be moved somewhere else. */
                $lines = array_merge($lines,
                                     array($label1),
                                     $edit->final1,
                                     array($label3),
                                     $edit->final2,
                                     array($label2));
                $this->_conflictingBlocks++;
            } else {
                $lines = array_merge($lines, $edit->merged());
            }
        }

        return $lines;
    }

    /**
     * @access private
     *
     * @param array $edits1
     * @param array $edits2
     *
     * @return array
     */
    function _diff3($edits1, $edits2) {
        $edits = array();
        $bb = new _Diff3_BlockBuilder();

        $e1 = current($edits1);
        $e2 = current($edits2);
        while ($e1 || $e2) {
            if ($e1 && $e2 && is_a($e1, '_DiffOp_copy') && is_a($e2, '_DiffOp_copy')) {
                /* We have copy blocks from both diffs. This is the (only)
                 * time we want to emit a diff3 copy block.  Flush current
                 * diff3 diff block, if any. */
                if ($edit = $bb->finish()) {
                    $edits[] = $edit;
                }

                $ncopy = min($e1->norig(), $e2->norig());
                assert($ncopy > 0);
                $edits[] = new _Diff3_Op_copy(array_slice($e1->orig, 0, $ncopy));

                if ($e1->norig() > $ncopy) {
                    array_splice($e1->orig, 0, $ncopy);
                    array_splice($e1->closing, 0, $ncopy);
                } else {
                    $e1 = next($edits1);
                }

                if ($e2->norig() > $ncopy) {
                    array_splice($e2->orig, 0, $ncopy);
                    array_splice($e2->closing, 0, $ncopy);
                } else {
                    $e2 = next($edits2);
                }
            } else {
                if ($e1 && $e2) {
                    if ($e1->orig && $e2->orig) {
                        $norig = min($e1->norig(), $e2->norig());
                        $orig = array_splice($e1->orig, 0, $norig);
                        array_splice($e2->orig, 0, $norig);
                        $bb->input($orig);
                    }

                    if (is_a($e1, '_DiffOp_copy')) {
                        $bb->out1(array_splice($e1->closing, 0, $norig));
                    }

                    if (is_a($e2, '_DiffOp_copy')) {
                        $bb->out2(array_splice($e2->closing, 0, $norig));
                    }
                }

                if ($e1 && ! $e1->orig) {
                    $bb->out1($e1->closing);
                    $e1 = next($edits1);
                }
                if ($e2 && ! $e2->orig) {
                    $bb->out2($e2->closing);
                    $e2 = next($edits2);
                }
            }
        }

        if ($edit = $bb->finish()) {
            $edits[] = $edit;
        }

        return $edits;
    }
}

/**
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class _Diff3_Op {

    function __construct($orig = false, $final1 = false, $final2 = false) {
        $this->orig = $orig ? $orig : array();
        $this->final1 = $final1 ? $final1 : array();
        $this->final2 = $final2 ? $final2 : array();
    }

    function merged() {
        if (!isset($this->_merged)) {
            if ($this->final1 === $this->final2) {
                $this->_merged = &$this->final1;
            } elseif ($this->final1 === $this->orig) {
                $this->_merged = &$this->final2;
            } elseif ($this->final2 === $this->orig) {
                $this->_merged = &$this->final1;
            } else {
                $this->_merged = false;
            }
        }

        return $this->_merged;
    }

    function isConflict() {
        return $this->merged() === false;
    }

}

/**
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class _Diff3_Op_copy extends _Diff3_Op {

    function __construct($lines = false) {
        $this->orig = $lines ? $lines : array();
        $this->final1 = &$this->orig;
        $this->final2 = &$this->orig;
    }

    function merged() {
        return $this->orig;
    }

    function isConflict() {
        return false;
    }
}

/**
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * @access private
 */
class _Diff3_BlockBuilder {

    function __construct() {
        $this->_init();
    }

    function input($lines) {
        if ($lines) {
            $this->_append($this->orig, $lines);
        }
    }

    function out1($lines) {
        if ($lines) {
            $this->_append($this->final1, $lines);
        }
    }

    function out2($lines) {
        if ($lines) {
            $this->_append($this->final2, $lines);
        }
    }

    function isEmpty() {
        return !$this->orig && !$this->final1 && !$this->final2;
    }

    function finish() {
        if ($this->isEmpty()) {
            return false;
        } else {
            $edit = new _Diff3_Op($this->orig, $this->final1, $this->final2);
            $this->_init();
            return $edit;
        }
    }

    function _init() {
        $this->orig = $this->final1 = $this->final2 = array();
    }

    function _append(&$array, $lines) {
        array_splice($array, sizeof($array), 0, $lines);
    }
}

//Setup VIM: ex: et ts=4 :
