<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\GfmHr;

/**
 * Tests for the unified horizontal-rule mode.
 *
 * Covers both pattern flavors: pure `dw` (4-or-more dashes only)
 * and the wider GFM flavor that loads in `md`, `dw+md`, `md+dw`
 * (3-or-more of `-` / `*` / `_`). No leading, trailing, or internal
 * whitespace tolerance in either flavor.
 */
class GfmHrTest extends ParserTestBase
{
    /**
     * Whether at least one `hr` call was emitted for the given input
     * under the given syntax. Returns the call count for richer
     * assertion messages.
     */
    protected function countHrCalls(string $syntax, string $input): int
    {
        $this->setUp();
        $this->setSyntax($syntax);
        $this->P->addMode('gfm_hr', new GfmHr());
        $this->P->parse($input);
        return count(array_filter($this->H->calls, static fn($c) => $c[0] === 'hr'));
    }

    // ------------------------------------------------------------------
    // DW flavor (`$conf['syntax'] = 'dw'`)
    // ------------------------------------------------------------------

    public function testDwFourDashes()
    {
        $this->assertSame(1, $this->countHrCalls('dw', "\n----\n"));
    }

    public function testDwManyDashes()
    {
        $this->assertSame(1, $this->countHrCalls('dw', "\n--------\n"));
    }

    public function testDwThreeDashesNotHr()
    {
        $this->assertSame(0, $this->countHrCalls('dw', "\n---\n"));
    }

    public function testDwAsterisksNotHr()
    {
        $this->assertSame(0, $this->countHrCalls('dw', "\n***\n"));
        $this->assertSame(0, $this->countHrCalls('dw', "\n********\n"));
    }

    public function testDwUnderscoresNotHr()
    {
        $this->assertSame(0, $this->countHrCalls('dw', "\n___\n"));
        $this->assertSame(0, $this->countHrCalls('dw', "\n_____\n"));
    }

    public function testDwLeadingSpaceNotHr()
    {
        $this->assertSame(0, $this->countHrCalls('dw', "\n ----\n"));
    }

    public function testDwTrailingSpaceNotHr()
    {
        $this->assertSame(0, $this->countHrCalls('dw', "\n---- \n"));
    }

    public function testDwInterruptsParagraph()
    {
        $this->setSyntax('dw');
        $this->P->addMode('gfm_hr', new GfmHr());
        $this->P->parse("Foo\n----\nBar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo"]],
            ['p_close', []],
            ['hr', []],
            ['p_open', []],
            ['cdata', ["\nBar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ------------------------------------------------------------------
    // GFM flavor (any non-`dw` syntax setting)
    // ------------------------------------------------------------------

    public function testMdDashes()
    {
        foreach (['md', 'dw+md', 'md+dw'] as $syntax) {
            $this->assertSame(1, $this->countHrCalls($syntax, "\n---\n"),
                "syntax=$syntax: bare `---` must produce hr");
        }
    }

    public function testMdAsterisks()
    {
        foreach (['md', 'dw+md', 'md+dw'] as $syntax) {
            $this->assertSame(1, $this->countHrCalls($syntax, "\n***\n"),
                "syntax=$syntax: bare `***` must produce hr");
        }
    }

    public function testMdUnderscores()
    {
        foreach (['md', 'dw+md', 'md+dw'] as $syntax) {
            $this->assertSame(1, $this->countHrCalls($syntax, "\n___\n"),
                "syntax=$syntax: bare `___` must produce hr");
        }
    }

    public function testMdManyChars()
    {
        $this->assertSame(1, $this->countHrCalls('md', "\n--------\n"));
        $this->assertSame(1, $this->countHrCalls('md', "\n********\n"));
        $this->assertSame(1, $this->countHrCalls('md', "\n________\n"));
    }

    public function testMdTooFew()
    {
        $this->assertSame(0, $this->countHrCalls('md', "\n--\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n**\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n__\n"));
    }

    public function testMdInternalSpacesNotSupported()
    {
        $this->assertSame(0, $this->countHrCalls('md', "\n- - -\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n* * *\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n_ _ _\n"));
    }

    public function testMdLeadingSpaceNotSupported()
    {
        $this->assertSame(0, $this->countHrCalls('md', "\n ***\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n   ---\n"));
    }

    public function testMdTrailingSpaceNotSupported()
    {
        $this->assertSame(0, $this->countHrCalls('md', "\n--- \n"));
    }

    public function testMdMixedChars()
    {
        $this->assertSame(0, $this->countHrCalls('md', "\n-*-\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n***---\n"));
    }

    public function testMdLetterMixed()
    {
        $this->assertSame(0, $this->countHrCalls('md', "\n---a\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\na---\n"));
        $this->assertSame(0, $this->countHrCalls('md', "\n---a---\n"));
    }

    public function testMdInterruptsParagraph()
    {
        $this->setSyntax('md');
        $this->P->addMode('gfm_hr', new GfmHr());
        $this->P->addMode('eol', new Eol());
        $this->P->parse("Foo\n***\nbar");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('hr', $modes,
            'thematic break interrupts paragraph without blank line (spec 28)');
    }

    // ------------------------------------------------------------------
    // Common
    // ------------------------------------------------------------------

    public function testSortValue()
    {
        $mode = new GfmHr();
        $this->assertSame(160, $mode->getSort());
    }
}
