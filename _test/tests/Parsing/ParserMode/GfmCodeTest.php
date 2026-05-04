<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\GfmCode;

/**
 * Tests for GFM backtick-fenced code blocks (`GfmCode`).
 */
class GfmCodeTest extends ParserTestBase
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

    /**
     * Register the mode plus Eol. Order matters: the ParallelRegex
     * alternates patterns in insertion order and leftmost-match picks the
     * first alternative, so the block mode must be added before Eol
     * (same effect ModeRegistry achieves in production via sort values).
     */
    private function addModes(): void
    {
        $this->P->addMode('gfm_code', new GfmCode());
        $this->P->addMode('eol', new Eol());
    }

    function testBasicBacktickFence()
    {
        $this->addModes();
        $this->P->parse("```\nhello\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("hello\n", $codeCalls[0][1][0]);
        $this->assertNull($codeCalls[0][1][1]);
        $this->assertNull($codeCalls[0][1][2]);
    }

    function testLanguageFromInfoString()
    {
        $this->addModes();
        $this->P->parse("```ruby\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("x\n", $codeCalls[0][1][0]);
        $this->assertSame('ruby', $codeCalls[0][1][1]);
    }

    function testLanguageIsFirstWord()
    {
        // GFM spec example 113: only the first token of the info string
        // is treated as a language; extra junk is dropped.
        $this->addModes();
        $this->P->parse("```ruby startline=3 \$%@#\$\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('ruby', $codeCalls[0][1][1]);
    }

    function testBacktickInfoRejectsBackticks()
    {
        // GFM spec example 115: a backtick run with backticks in its
        // info string is NOT a fence — stays for inline code parsing.
        $this->addModes();
        $this->P->parse("``` aa ```\nfoo");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('code', $modes,
            'Backtick fence must reject backticks in info string');
    }

    function testLongerCloseFenceIsValid()
    {
        // Opener 3, closer 5 — valid because closer is ≥ opener.
        $this->addModes();
        $this->P->parse("```\naaa\n`````");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("aaa\n", $codeCalls[0][1][0]);
    }

    function testIndentedFenceIsNotFence()
    {
        // Column-0-only policy: any leading space rejects the fence.
        $this->addModes();
        $this->P->parse(" ```\nx\n ```");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('code', $modes,
            'Fence must start at column 0; indent is out of scope');
    }

    function testUnclosedFenceStaysLiteral()
    {
        // An unclosed fence must not emit a code call — the ``` stays as
        // paragraph text. Diverges from strict GFM (which would consume
        // to EOF); see class docblock for the rationale.
        $this->addModes();
        $this->P->parse("```\nabc\ndef");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('code', $modes,
            'Unclosed fences must stay literal, not emit code');
    }

    function testEmptyBody()
    {
        $this->addModes();
        $this->P->parse("```\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('', $codeCalls[0][1][0]);
    }

    function testCloseWithTrailingSpaces()
    {
        $this->addModes();
        $this->P->parse("```\nx\n```   ");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("x\n", $codeCalls[0][1][0]);
    }

    function testCloseWithTrailingTabs()
    {
        $this->addModes();
        $this->P->parse("```\nx\n```\t\t");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("x\n", $codeCalls[0][1][0]);
    }

    function testFenceInterruptsParagraph()
    {
        // GFM spec example 110: a fence doesn't need a blank line before
        // it; the `code` instruction is block-level and paragraphs break.
        $this->addModes();
        $this->P->parse("foo\n```\nbar\n```\nbaz");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("bar\n", $codeCalls[0][1][0]);
    }

    function testEmptyInfoStringMeansNullLanguage()
    {
        $this->addModes();
        $this->P->parse("```\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertNull($codeCalls[0][1][1]);
    }

    function testInfoStringSpecialChar()
    {
        // GFM spec example 114: a semicolon is a valid language token.
        $this->addModes();
        $this->P->parse("```;\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame(';', $codeCalls[0][1][1]);
    }

    function testTildeLineDoesNotCloseBacktickFence()
    {
        $this->addModes();
        $this->P->parse("```\naaa\n~~~\nbbb\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("aaa\n~~~\nbbb\n", $codeCalls[0][1][0]);
    }

    function testFilenameAfterLanguage()
    {
        // DokuWiki's Code mode treats the second whitespace token as
        // the filename (turns the block into a download link). GfmCode
        // accepts the same vocabulary on the info string.
        $this->addModes();
        $this->P->parse("```php myfile.php\n<?php\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('php', $codeCalls[0][1][1]);
        $this->assertSame('myfile.php', $codeCalls[0][1][2]);
    }

    function testHtmlAliasedToHtml4Strict()
    {
        // Same GeSHi alias DokuWiki's Code mode applies.
        $this->addModes();
        $this->P->parse("```html\n<p>\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('html4strict', $codeCalls[0][1][1]);
    }

    function testDashMeansNoLanguage()
    {
        // DokuWiki uses `-` as an explicit "no language" marker; lets
        // a filename follow without a language argument first.
        $this->addModes();
        $this->P->parse("```- somefile.txt\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertNull($codeCalls[0][1][1]);
        $this->assertSame('somefile.txt', $codeCalls[0][1][2]);
    }

    function testHighlightOptions()
    {
        // DokuWiki uses space-separated keys inside `[...]`; comma
        // separators inside a value survive (as GeSHi line lists).
        $this->addModes();
        $this->P->parse("```php [enable_line_numbers start_line_numbers_at=\"10\"]\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('php', $codeCalls[0][1][1]);
        $this->assertNull($codeCalls[0][1][2]);
        $this->assertCount(4, $codeCalls[0][1]);
        $this->assertSame(
            ['enable_line_numbers' => true, 'start_line_numbers_at' => 10],
            $codeCalls[0][1][3]
        );
    }

    function testFilenameAndOptions()
    {
        $this->addModes();
        $this->P->parse("```php myfile.php [enable_line_numbers]\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('php', $codeCalls[0][1][1]);
        $this->assertSame('myfile.php', $codeCalls[0][1][2]);
        $this->assertSame(['enable_line_numbers' => true], $codeCalls[0][1][3]);
    }

    function testInfoStringBackslashEscapeIsResolved()
    {
        // GFM §6.1 (spec example 322): backslash-escaped punctuation in
        // the info string is unescaped before parseAttributes splits it
        // into language / filename / options.
        $this->addModes();
        $this->P->parse("```c\\#\nx\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame('c#', $codeCalls[0][1][1]);
    }

    function testCodeBodyKeepsBackslashEscapes()
    {
        // The body of a fenced code block is captured verbatim — escapes
        // inside it must NOT collapse (spec: escapes don't fire in code
        // blocks). Only the info string is touched by Escape::unescape.
        $this->addModes();
        $this->P->parse("```\nfoo \\* bar\n```");
        $codeCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'code'
        ));
        $this->assertCount(1, $codeCalls);
        $this->assertSame("foo \\* bar\n", $codeCalls[0][1][0]);
    }

    function testSortValue()
    {
        $this->assertSame(200, (new GfmCode())->getSort());
    }
}
