<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmBacktickSingle;
use dokuwiki\Parsing\ParserMode\GfmEmphasis;
use dokuwiki\Parsing\ParserMode\GfmEscape;
use dokuwiki\Parsing\ParserMode\GfmHeader;
use dokuwiki\Parsing\ParserMode\Linebreak;

/**
 * Tests for the GFM backslash-escape mode.
 */
class GfmEscapeTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    /**
     * Every ASCII punctuation character is escapable per GFM §6.1.
     *
     * @dataProvider provideEscapableChars
     */
    function testEscapableAsciiPunctuationProducesLiteral(string $char)
    {
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->parse('foo \\' . $char . ' bar');

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));

        $this->assertSame("\nfoo " . $char . ' bar', $joined,
            "Escaped {$char} must collapse to the literal char in cdata stream");
    }

    public static function provideEscapableChars(): array
    {
        $chars = str_split('!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~');
        return array_combine(
            array_map(static fn($c) => 'char_' . bin2hex($c), $chars),
            array_map(static fn($c) => [$c], $chars),
        );
    }

    /**
     * Backslash before non-ASCII-punctuation stays literal — letters,
     * digits, multibyte chars, spaces, and tabs are not escapable. The
     * pattern simply doesn't match, so the bytes flow through as cdata.
     *
     * @dataProvider provideNonEscapableChars
     */
    function testNonEscapableCharsKeepBackslash(string $tail)
    {
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->parse('a \\' . $tail . ' b');

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));

        $this->assertSame("\na \\" . $tail . ' b', $joined);
    }

    public static function provideNonEscapableChars(): array
    {
        return [
            'letter_upper' => ['A'],
            'letter_lower' => ['a'],
            'digit'        => ['3'],
            'multibyte'    => ['α'],
            'space'        => [' '],
            'tab'          => ["\t"],
        ];
    }

    function testDoubleBackslashCollapsesToSingleBackslash()
    {
        // \\ is the escaped-backslash form. The first char in the match
        // is consumed as the escape introducer; the second is emitted as
        // a literal backslash.
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->parse('foo \\\\ bar');

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));

        $this->assertSame("\nfoo \\ bar", $joined);
    }

    function testEscapedAsteriskBlocksEmphasis()
    {
        // GFM spec example 310 fragment. \* must consume the asterisk
        // before GfmEmphasis can use it as an opener.
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('\\*not emphasized*');

        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('emphasis_open', $modes,
            'Escaped opener must not start emphasis');
    }

    function testEscapedBackslashThenEmphasisOpens()
    {
        // GFM spec example 311. \\ collapses to a literal backslash, and
        // the *emphasis* that follows is now seen by GfmEmphasis with
        // its full text intact.
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('\\\\*emphasis*');

        $modes = array_column($this->H->calls, 0);
        $this->assertContains('emphasis_open', $modes,
            'After \\\\ collapses, the surviving *emphasis* must open emphasis');
    }

    function testEscapedHashBlocksHeader()
    {
        // \# must defeat GfmHeader's column-0 # match. The trailing text
        // becomes a normal paragraph instead.
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("\\# not a heading");

        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('header', $modes,
            'Escaped # must not produce a header');
    }

    function testNoEscapeInsideBacktickSpan()
    {
        // GFM spec example 313. The whole `\[\`` is captured by
        // GfmBacktickSingle in one regex shot, so GfmEscape never runs
        // on its body. The body must retain the literal backslashes.
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('`\\[\\`');

        $unformatted = array_filter($this->H->calls, static fn($c) => $c[0] === 'unformatted');
        $bodies = array_map(static fn($c) => $c[1][0], $unformatted);
        $this->assertContains('\\[\\', $bodies,
            'Backtick span body must preserve the literal backslashes');
    }

    function testSortValue()
    {
        $mode = new GfmEscape();
        $this->assertSame(5, $mode->getSort());
    }

    /**
     * In pure `md` mode, `\\` before a newline still escapes to a literal
     * backslash per GFM §6.1 — no DW Linebreak is loaded to defer to.
     */
    function testDoubleBackslashBeforeNewlineEscapesInPureMd()
    {
        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->parse("foo \\\\\nbar");

        $names = array_column($this->H->calls, 0);
        $this->assertNotContains('linebreak', $names,
            'No DW Linebreak is loaded in pure md mode — `\\\\\\n` must stay an escape');

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));
        $this->assertSame("\nfoo \\\nbar", $joined,
            '`\\\\` collapses to a literal backslash; the newline survives as cdata');
    }

    /**
     * In any DW-loaded mode (`dw+md` / `md+dw`), `\\` before a space, tab,
     * or newline must defer to DW's Linebreak mode. GfmEscape would
     * otherwise consume those two bytes first (sort 5 vs Linebreak's 140)
     * and the forced linebreak would never fire.
     *
     * @dataProvider provideDwLoadedSyntaxes
     */
    function testDoubleBackslashBeforeNewlineDefersToLinebreakWhenDwLoaded(string $syntax)
    {
        $this->setSyntax($syntax);

        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('linebreak', new Linebreak());
        $this->P->parse("foo\\\\\nbar");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('linebreak', $names,
            "Under $syntax, `\\\\\\\\\\n` must yield a DW linebreak instead of an escape");
    }

    /**
     * Same deferral applies for `\\` before a literal space — the
     * canonical DW forced-linebreak form.
     *
     * @dataProvider provideDwLoadedSyntaxes
     */
    function testDoubleBackslashBeforeSpaceDefersToLinebreakWhenDwLoaded(string $syntax)
    {
        $this->setSyntax($syntax);

        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('linebreak', new Linebreak());
        $this->P->parse('foo \\\\ bar');

        $names = array_column($this->H->calls, 0);
        $this->assertContains('linebreak', $names,
            "Under $syntax, `\\\\\\\\ ` must yield a DW linebreak instead of an escape");
    }

    /**
     * The deferral is narrow: `\\` followed by non-whitespace still
     * escapes to a literal backslash, even with DW Linebreak loaded.
     * UNC-style paths like `\\\\host\\share` would otherwise become a
     * surprise of literal double-backslashes for a user who typed two
     * GFM-escapes back-to-back.
     *
     * @dataProvider provideDwLoadedSyntaxes
     */
    function testMidLineDoubleBackslashStillEscapesWhenDwLoaded(string $syntax)
    {
        $this->setSyntax($syntax);

        $this->P->addMode('gfm_escape', new GfmEscape());
        $this->P->addMode('linebreak', new Linebreak());
        $this->P->parse('\\\\\\\\host\\\\share');

        $names = array_column($this->H->calls, 0);
        $this->assertNotContains('linebreak', $names,
            'Mid-line `\\\\` (no EOL whitespace) must not fire a linebreak');

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));
        $this->assertSame("\n\\\\host\\share", $joined,
            'Each `\\\\` collapses to a single literal backslash, GFM-style');
    }

    public static function provideDwLoadedSyntaxes(): array
    {
        return [
            'dw_md' => ['dw+md'],
            'md_dw' => ['md+dw'],
        ];
    }
}
