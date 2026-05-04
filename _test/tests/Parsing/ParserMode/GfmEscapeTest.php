<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmBacktickSingle;
use dokuwiki\Parsing\ParserMode\GfmEmphasis;
use dokuwiki\Parsing\ParserMode\GfmEscape;
use dokuwiki\Parsing\ParserMode\GfmHeader;

/**
 * Tests for the GFM backslash-escape mode.
 */
class GfmEscapeTest extends ParserTestBase
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
}
