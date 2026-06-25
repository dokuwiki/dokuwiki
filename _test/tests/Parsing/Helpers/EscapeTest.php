<?php

namespace dokuwiki\test\Parsing\Helpers;

use dokuwiki\Parsing\Helpers\Escape;

/**
 * Tests for the GFM backslash-escape post-hoc helper.
 *
 * The lexer-mode coverage is in {@see \dokuwiki\test\Parsing\ParserMode\GfmEscapeTest};
 * this class exercises the helper that GfmLink and GfmCode call on text
 * the lexer never reached.
 */
class EscapeTest extends \DokuWikiTest
{
    /**
     * Every ASCII punctuation char is escapable per GFM §6.1.
     *
     * @dataProvider provideEscapableChars
     */
    function testUnescapesEscapablePunctuation(string $char)
    {
        $this->assertSame(
            "before{$char}after",
            Escape::unescapeBackslashes("before\\{$char}after")
        );
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
     * Backslash before any non-punctuation char stays as-is — the helper
     * must not touch it. Mirrors the lexer mode's pattern, which also
     * doesn't match these.
     *
     * @dataProvider provideNonEscapableTails
     */
    function testKeepsBackslashBeforeNonPunctuation(string $tail)
    {
        $input = "x\\{$tail}y";
        $this->assertSame($input, Escape::unescapeBackslashes($input));
    }

    public static function provideNonEscapableTails(): array
    {
        return [
            'letter_upper' => ['A'],
            'letter_lower' => ['a'],
            'digit'        => ['3'],
            'multibyte'    => ['α'],
            'space'        => [' '],
            'tab'          => ["\t"],
            'newline'      => ["\n"],
        ];
    }

    function testDoubleBackslashCollapsesOnce()
    {
        // `\\` → `\`. The collapse is a single replacement; the surviving
        // backslash does NOT consume the next char.
        $this->assertSame('a\\*b', Escape::unescapeBackslashes('a\\\\*b'));
    }

    function testTripleBackslashLeavesOneEscape()
    {
        // `\\\*` → `\` + `*` (first pair collapses to `\`, the surviving
        // standalone `\*` then unescapes to `*` because preg_replace
        // processes all non-overlapping matches in one pass).
        $this->assertSame('a\\*b', Escape::unescapeBackslashes('a\\\\\\*b'));
    }

    function testMultipleEscapesInOnePass()
    {
        $this->assertSame(
            '/path*with|special#chars',
            Escape::unescapeBackslashes('/path\\*with\\|special\\#chars')
        );
    }

    function testStringWithoutBackslashesIsUnchanged()
    {
        $this->assertSame('plain text', Escape::unescapeBackslashes('plain text'));
    }

    function testEmptyStringRoundTrips()
    {
        $this->assertSame('', Escape::unescapeBackslashes(''));
    }

    function testTrailingLoneBackslashSurvives()
    {
        // A backslash with nothing after it can't form an escape — it
        // stays literal.
        $this->assertSame('x\\', Escape::unescapeBackslashes('x\\'));
    }
}
