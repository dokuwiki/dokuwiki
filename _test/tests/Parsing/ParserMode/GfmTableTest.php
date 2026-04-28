<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmTable;

/**
 * Tests for GFM table blocks.
 *
 * GfmTable uses an entry/exit lexer state with allowedModes-driven inline
 * nesting, then runs a small post-pass rewriter (Handler\GfmTable) that
 * derives column alignment from the delimiter row, drops it, pads/truncates
 * body rows, and emits the canonical DokuWiki table call sequence. Tests
 * here assert against the rewriter's output, not the raw `gfm_table_*`
 * tokens it consumes.
 */
class GfmTableTest extends ParserTestBase
{
    public function testSort()
    {
        $this->assertSame(55, (new GfmTable())->getSort());
    }

    /** Spec example 198: basic table, no alignment, plain text. */
    public function testBasicTable()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| foo | bar |\n| --- | --- |\n| baz | bim |");

        $expected = [
            ['document_start', []],
            ['table_open', [2, 2, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['foo']],
            ['tableheader_close', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['bar']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['tabletbody_open', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['baz']],
            ['tablecell_close', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['bim']],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tabletbody_close', []],
            ['table_close', [42]],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 199: alignment via `:-:` and `---:`, no outer pipes. */
    public function testAlignment()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| abc | defghi |\n:-: | -----------:\nbar | baz");

        $expected = [
            ['document_start', []],
            ['table_open', [2, 2, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, 'center', 1]],
            ['cdata', ['abc']],
            ['tableheader_close', []],
            ['tableheader_open', [1, 'right', 1]],
            ['cdata', ['defghi']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['tabletbody_open', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, 'center', 1]],
            ['cdata', ['bar']],
            ['tablecell_close', []],
            ['tablecell_open', [1, 'right', 1]],
            ['cdata', ['baz']],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tabletbody_close', []],
            ['table_close', [46]],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 200 (partial): a backslash-escaped pipe must not split
     *  the cell. The unescape itself — turning `\|` into a literal `|` in
     *  the cell content — is GfmEscape's job and is not yet implemented;
     *  here we only assert the cell-splitting contract that GfmTable owns. */
    public function testEscapedPipeDoesNotSplitCell()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| f\\|oo |\n| ---- |");

        // One cell, content `f\|oo` literal (escape preserved). When
        // GfmEscape lands the same input will collapse to `f|oo` without
        // any change here.
        $expected = [
            ['document_start', []],
            ['table_open', [1, 1, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['f\\|oo']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['table_close', [19]],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 201: a blockquote line terminates the table. */
    public function testTerminatedByBlockquote()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| abc | def |\n| --- | --- |\n| bar | baz |\n> bar");

        // Expect the table to end at the start of `> bar`; the trailing
        // `> bar` is left as cdata (no quote mode added in this test).
        $expected = [
            ['document_start', []],
            ['table_open', [2, 2, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['abc']],
            ['tableheader_close', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['def']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['tabletbody_open', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['bar']],
            ['tablecell_close', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['baz']],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tabletbody_close', []],
            ['table_close', [42]],
            ['p_open', []],
            ['cdata', ['> bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 202: short body row gets padded to header column count. */
    public function testShortBodyRowPadded()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| abc | def |\n| --- | --- |\n| bar | baz |\nbar");

        $expected = [
            ['document_start', []],
            ['table_open', [2, 3, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['abc']],
            ['tableheader_close', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['def']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['tabletbody_open', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['bar']],
            ['tablecell_close', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['baz']],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['bar']],
            ['tablecell_close', []],
            ['tablecell_open', [1, null, 1]],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tabletbody_close', []],
            ['table_close', [46]],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 204: long body row truncated to header column count. */
    public function testLongBodyRowTruncated()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| abc | def |\n| --- | --- |\n| bar |\n| bar | baz | boo |");

        $expected = [
            ['document_start', []],
            ['table_open', [2, 3, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['abc']],
            ['tableheader_close', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['def']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['tabletbody_open', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['bar']],
            ['tablecell_close', []],
            ['tablecell_open', [1, null, 1]],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tablerow_open', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['bar']],
            ['tablecell_close', []],
            ['tablecell_open', [1, null, 1]],
            ['cdata', ['baz']],
            ['tablecell_close', []],
            ['tablerow_close', []],
            ['tabletbody_close', []],
            ['table_close', [56]],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 203: header has 2 cells, delimiter has 1 - the regex
     *  matches but the rewriter detects the mismatch and emits cdata, which
     *  the Block rewriter wraps in a paragraph. */
    public function testColumnCountMismatchFallback()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| abc | def |\n| --- |\n| bar |");

        $expected = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["| abc | def |\n| --- |\n| bar |"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /** Spec example 205: header + delimiter only, no body rows. */
    public function testEmptyBody()
    {
        $this->P->addMode('gfm_table', new GfmTable());
        $this->P->parse("| abc | def |\n| --- | --- |");

        $expected = [
            ['document_start', []],
            ['table_open', [2, 1, 1]],
            ['tablethead_open', []],
            ['tablerow_open', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['abc']],
            ['tableheader_close', []],
            ['tableheader_open', [1, null, 1]],
            ['cdata', ['def']],
            ['tableheader_close', []],
            ['tablerow_close', []],
            ['tablethead_close', []],
            ['table_close', [28]],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }
}
