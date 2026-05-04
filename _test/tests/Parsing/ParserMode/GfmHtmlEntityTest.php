<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmHtmlEntity;
use dokuwiki\Utf8\Unicode;

/**
 * Consecutive cdata calls are coalesced by Handler\Block::addCall during
 * finalize(), so a successful match shows up as a single cdata containing
 * the decoded character spliced into the surrounding text. Non-matching
 * inputs leave the literal `&...;` bytes in the cdata.
 */
class GfmHtmlEntityTest extends ParserTestBase
{
    private function assertParsedCdata(string $input, string $expectedCdata): void
    {
        $this->P->addMode('gfm_html_entity', new GfmHtmlEntity());
        $this->P->parse($input);
        $this->assertCalls([
            ['document_start', []],
            ['p_open', []],
            ['cdata', [$expectedCdata]],
            ['p_close', []],
            ['document_end', []],
        ], $this->H->calls);
    }

    public function testDecimalAscii()
    {
        $this->assertParsedCdata('x &#35; y', "\nx # y");
    }

    public function testDecimalMultibyte()
    {
        $this->assertParsedCdata('a&#1234;b', "\na\u{04D2}b");
    }

    public function testHexLowercase()
    {
        $this->assertParsedCdata('a&#xcab;b', "\na\u{0CAB}b");
    }

    public function testHexUppercase()
    {
        $this->assertParsedCdata('a&#XD06;b', "\na\u{0D06}b");
    }

    public function testHexQuoteCharacter()
    {
        $this->assertParsedCdata('a&#X22;b', "\na\"b");
    }

    public function testZeroMapsToReplacement()
    {
        $this->assertParsedCdata('a&#0;b', "\na\u{FFFD}b");
    }

    public function testSurrogateMapsToReplacement()
    {
        $this->assertParsedCdata('a&#xD800;b', "\na\u{FFFD}b");
    }

    public function testMaxValidCodepoint()
    {
        $this->assertParsedCdata('a&#x10FFFF;b', "\na" . Unicode::toUtf8([0x10FFFF]) . 'b');
    }

    public function testNonEntityTooManyDecimalDigitsStaysLiteral()
    {
        $this->assertParsedCdata('a&#987654321;b', "\na&#987654321;b");
    }

    public function testNonEntityHexLetterAfterAmpStaysLiteral()
    {
        $this->assertParsedCdata('a&#abcdef0;b', "\na&#abcdef0;b");
    }

    public function testEmptyEntityStaysLiteral()
    {
        $this->assertParsedCdata('a&#;b', "\na&#;b");
    }

    public function testMissingSemicolonStaysLiteral()
    {
        $this->assertParsedCdata('a&#35 b', "\na&#35 b");
    }

    public function testMultipleNumericEntitiesInSequence()
    {
        $this->assertParsedCdata('&#35;&#1234;&#xcab;', "\n#\u{04D2}\u{0CAB}");
    }

    public function testTabDecodes()
    {
        $this->assertParsedCdata('a&#9;b', "\na\tb");
    }

    public function testNewlineDecodes()
    {
        $this->assertParsedCdata('foo&#10;&#10;bar', "\nfoo\n\nbar");
    }

    public function testNamedAmp()
    {
        // &amp; decodes to '&', renderer re-escapes on output
        $this->assertParsedCdata('a&amp;b', "\na&b");
    }

    public function testNamedCopy()
    {
        $this->assertParsedCdata('a&copy;b', "\na\u{00A9}b");
    }

    public function testNamedAElig()
    {
        $this->assertParsedCdata('a&AElig;b', "\na\u{00C6}b");
    }

    public function testNamedNbsp()
    {
        $this->assertParsedCdata('a&nbsp;b', "\na\u{00A0}b");
    }

    public function testNamedMultiCodepoint()
    {
        // &ngE; -> U+2267 + U+0338 (combining solidus)
        $this->assertParsedCdata('a&ngE;b', "\na\u{2267}\u{0338}b");
    }

    public function testNamedUnknownStaysLiteral()
    {
        $this->assertParsedCdata('a&MadeUpEntity;b', "\na&MadeUpEntity;b");
    }

    public function testNamedNoSemicolonStaysLiteral()
    {
        $this->assertParsedCdata('a&copy b', "\na&copy b");
    }

    public function testMixedNumericAndNamed()
    {
        $this->assertParsedCdata('&#35;&copy;&#x22;', "\n#\u{00A9}\"");
    }
}
