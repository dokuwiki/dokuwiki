<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\Handler\GfmLists;
use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmListblock;

/**
 * Tests for GFM list blocks.
 *
 * GfmListblock captures the entire list block via addSpecialPattern then
 * sub-parses each item's body through a sub-parser acquired from
 * ModeRegistry's pool, so the outer parser only needs gfm_listblock added;
 * inline modes (emphasis, strong, etc.) and block modes (gfm_code) are
 * picked up by the sub-parser.
 */
class GfmListblockTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $conf['syntax'] = 'md';
        ModeRegistry::reset();
    }

    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    public function testUnorderedDash()
    {
        // Each item's body is sub-parsed and wrapped in a `nest` call so
        // the main handler's Block rewriter doesn't double-wrap multi-block
        // content. See AbstractListsRewriter / Block / Nest interaction.
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- A\n- B\n- C\n");

        $expected = [
            ['document_start', []],
            ['listu_open', []],
            ['listitem_open', [1]],
            ['listcontent_open', []],
            ['nest', [[ ['cdata', ['A']] ]]],
            ['listcontent_close', []],
            ['listitem_close', []],
            ['listitem_open', [1]],
            ['listcontent_open', []],
            ['nest', [[ ['cdata', ['B']] ]]],
            ['listcontent_close', []],
            ['listitem_close', []],
            ['listitem_open', [1]],
            ['listcontent_open', []],
            ['nest', [[ ['cdata', ['C']] ]]],
            ['listcontent_close', []],
            ['listitem_close', []],
            ['listu_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    public function testUnorderedAsterisk()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("* A\n* B\n");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('listu_open', $names);
        $this->assertNotContains('listo_open', $names);
    }

    public function testUnorderedPlus()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("+ A\n+ B\n");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('listu_open', $names);
        $this->assertNotContains('listo_open', $names);
    }

    public function testOrderedDot()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("1. A\n2. B\n3. C\n");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('listo_open', $names);
        $this->assertNotContains('listu_open', $names);
    }

    public function testOrderedParen()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("1) A\n2) B\n");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('listo_open', $names);
    }

    public function testOrderedStartNumber()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("5. A\n6. B\n");

        $opens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listo_open_start');
        $this->assertCount(1, $opens, 'non-default start emits listo_open_start, not listo_open');
        $open = array_values($opens)[0];
        $this->assertSame([5], $open[1], 'listo_open_start must carry the first item start number');

        $plainOpens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listo_open');
        $this->assertCount(0, $plainOpens, 'plain listo_open is not emitted when start != 1');
    }

    public function testOrderedDefaultStartNotEmittedSpecially()
    {
        // For start=1 the rewriter emits the plain listo_open instruction so
        // unmodified plugin renderers (which only override listo_open) keep
        // working. The wire shape is bare [].
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("1. A\n2. B\n");

        $opens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listo_open');
        $open = array_values($opens)[0];
        $this->assertSame([], $open[1]);

        $startOpens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listo_open_start');
        $this->assertCount(0, $startOpens, 'start=1 must not emit listo_open_start');
    }

    public function testNestedTwoLevels()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- A\n  - B\n- C\n");

        $expected = [
            ['document_start', []],
            ['listu_open', []],
            ['listitem_open', [1, GfmLists::NODE]],
            ['listcontent_open', []],
            ['nest', [[ ['cdata', ['A']] ]]],
            ['listcontent_close', []],
            ['listu_open', []],
            ['listitem_open', [2]],
            ['listcontent_open', []],
            ['nest', [[ ['cdata', ['B']] ]]],
            ['listcontent_close', []],
            ['listitem_close', []],
            ['listu_close', []],
            ['listitem_close', []],
            ['listitem_open', [1]],
            ['listcontent_open', []],
            ['nest', [[ ['cdata', ['C']] ]]],
            ['listcontent_close', []],
            ['listitem_close', []],
            ['listu_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    /**
     * Flatten a call list, recursing into `nest` calls' inner content.
     * Useful for tests that just want to verify a particular instruction
     * appears somewhere in the rendered output regardless of nesting.
     */
    private function flatNames(array $calls): array
    {
        $names = [];
        foreach ($calls as $call) {
            $names[] = $call[0];
            if ($call[0] === 'nest') {
                $names = array_merge($names, $this->flatNames($call[1][0]));
            }
        }
        return $names;
    }

    public function testNestedThreeLevels()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- A\n  - B\n    - C\n");

        $itemOpens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listitem_open');
        $levels = array_map(static fn($c) => $c[1][0], array_values($itemOpens));
        $this->assertSame([1, 2, 3], $levels);
    }

    public function testInlineFormatting()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- **bold** text\n");

        $names = $this->flatNames($this->H->calls);
        $this->assertContains('strong_open', $names, 'inline strong must be parsed inside item');
        $this->assertContains('strong_close', $names);
    }

    public function testMarkerCharSwitchKeepsOneList()
    {
        // CommonMark: changing marker character (`-` → `+`) starts a new list.
        // Our simpler model groups by type ('u' / 'o') only, so `-` and `+`
        // share one <ul>. Deliberate simplification — the rewriter doesn't
        // distinguish marker characters within the same type.
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- A\n+ B\n");

        $opens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listu_open');
        $this->assertCount(1, $opens, 'marker-character change does not split unordered lists');
    }

    public function testOrderedToUnorderedSplits()
    {
        // Type change (o → u) DOES split, since the rewriter does close/open
        // when the type differs.
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("1. A\n- B\n");

        $oOpens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listo_open');
        $uOpens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listu_open');
        $this->assertCount(1, $oOpens);
        $this->assertCount(1, $uOpens);
    }

    public function testNotAListMidParagraph()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("Foo - bar");

        $names = array_column($this->H->calls, 0);
        $this->assertNotContains('listu_open', $names);
        $this->assertNotContains('listo_open', $names);
    }

    public function testEmptyMarkerEol()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("-\n");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('listu_open', $names, 'a bare marker still opens a list');
        $this->assertContains('listitem_open', $names);
    }

    public function testHeaderRejectedInsideItem()
    {
        // Sub-parser excludes BASEONLY (gfm_header), so `# bar` inside an item
        // body must NOT produce a header instruction.
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- foo\n  # bar\n");

        $names = $this->flatNames($this->H->calls);
        $this->assertNotContains('header', $names);
        $this->assertNotContains('section_open', $names);
    }

    public function testFencedCodeInsideItem()
    {
        // After the dedent step strips the 2-space prefix from the body,
        // the fence sits at column 0 from the sub-parser's point of view
        // and gfm_code matches it.
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- foo\n  ```\n  hello\n  ```\n");

        $names = $this->flatNames($this->H->calls);
        $this->assertContains('code', $names, 'fenced code inside item must be parsed');
    }

    public function testMultiParagraphItemIsLoose()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- foo\n\n  bar\n");

        // Loose item: the nest contains two p_open / p_close pairs (one per
        // paragraph) since the outer-only stripping in filterSubCalls only
        // collapses single-paragraph items.
        $names = $this->flatNames($this->H->calls);
        $pOpens = array_filter($names, static fn($n) => $n === 'p_open');
        $this->assertGreaterThanOrEqual(2, count($pOpens),
            'multi-paragraph items must keep both p_open calls');
    }

    public function testSortValue()
    {
        $mode = new GfmListblock();
        $this->assertSame(10, $mode->getSort());
    }

    /**
     * Regression: an item's sub-parsed content must reach the main handler
     * inside a `nest` call. Without the wrap, the main handler's Block
     * rewriter wraps the item content in another `<p>` (it already has
     * its own `<p>` from the sub-parser), producing nested paragraph tags.
     */
    public function testItemContentIsWrappedInNest()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- foo\n");

        $nests = array_filter($this->H->calls, static fn($c) => $c[0] === 'nest');
        $this->assertCount(1, $nests, 'each item body should land in one nest call');
    }

    /**
     * Regression: multiple consecutive blank lines inside a list block must
     * NOT terminate the list. Spec example 242 (`- Foo\n\n      bar\n\n\n
     * baz`) ends with a triple blank between two indented continuations and
     * expects all three to remain inside one list item.
     */
    public function testTripleBlankBetweenContinuationsKeepsListOpen()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- Foo\n\n      bar\n\n\n      baz\n");

        // The list should bracket all three indented lines: `- Foo`, `bar`,
        // and `baz` all live inside a single `<ul>`. We assert there is
        // exactly one listu_open / listu_close pair (no early termination
        // splitting `baz` into a separate top-level block).
        $opens  = array_filter($this->H->calls, static fn($c) => $c[0] === 'listu_open');
        $closes = array_filter($this->H->calls, static fn($c) => $c[0] === 'listu_close');
        $this->assertCount(1, $opens,
            'triple blank line between continuations must not split the list');
        $this->assertCount(1, $closes);
    }

    /**
     * Regression: blank lines between items (any number) must not split the
     * list. Spec example 270 stresses two-blank cases.
     */
    public function testMultipleBlanksBetweenItemsKeepsOneList()
    {
        $this->P->addMode('gfm_listblock', new GfmListblock());
        $this->P->parse("- one\n\n\n- two\n");

        $opens = array_filter($this->H->calls, static fn($c) => $c[0] === 'listu_open');
        $this->assertCount(1, $opens, 'blank lines between items must stay inside the list');
    }
}
