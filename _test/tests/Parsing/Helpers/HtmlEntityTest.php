<?php

namespace dokuwiki\test\Parsing\Helpers;

use dokuwiki\Parsing\Helpers\HtmlEntity;
use dokuwiki\Utf8\Unicode;

/**
 * Tests for the HTML entity-decoding post-hoc helper.
 *
 * The lexer-mode coverage is in {@see \dokuwiki\test\Parsing\ParserMode\GfmHtmlEntityTest};
 * this class exercises the helper that GfmLink and GfmCode call on text
 * the lexer never reached.
 */
class HtmlEntityTest extends \DokuWikiTest
{
    public function testDecimalDecodes()
    {
        $this->assertSame('a#b', HtmlEntity::decode('a&#35;b'));
    }

    public function testDecimalMultibyte()
    {
        $this->assertSame("a\u{04D2}b", HtmlEntity::decode('a&#1234;b'));
    }

    public function testHexLowercase()
    {
        $this->assertSame("a\u{0CAB}b", HtmlEntity::decode('a&#xcab;b'));
    }

    public function testHexUppercase()
    {
        $this->assertSame("a\u{0D06}b", HtmlEntity::decode('a&#XD06;b'));
    }

    public function testZeroMapsToReplacement()
    {
        $this->assertSame("a\u{FFFD}b", HtmlEntity::decode('a&#0;b'));
    }

    public function testSurrogateMapsToReplacement()
    {
        $this->assertSame("a\u{FFFD}b", HtmlEntity::decode('a&#xD800;b'));
    }

    public function testOverflowMapsToReplacement()
    {
        $this->assertSame("a\u{FFFD}b", HtmlEntity::decode('a&#1114112;b'));
    }

    public function testMaxValidCodepoint()
    {
        $this->assertSame(
            'a' . Unicode::toUtf8([0x10FFFF]) . 'b',
            HtmlEntity::decode('a&#x10FFFF;b')
        );
    }

    public function testNamedAmp()
    {
        $this->assertSame('a&b', HtmlEntity::decode('a&amp;b'));
    }

    public function testNamedCopy()
    {
        $this->assertSame("a\u{00A9}b", HtmlEntity::decode('a&copy;b'));
    }

    public function testNamedAElig()
    {
        $this->assertSame("a\u{00C6}b", HtmlEntity::decode('a&AElig;b'));
    }

    public function testNamedNbsp()
    {
        $this->assertSame("a\u{00A0}b", HtmlEntity::decode('a&nbsp;b'));
    }

    public function testNamedMultiCodepoint()
    {
        // &ngE; -> U+2267 + U+0338 (combining solidus)
        $this->assertSame("a\u{2267}\u{0338}b", HtmlEntity::decode('a&ngE;b'));
    }

    public function testUnknownNameStaysLiteral()
    {
        $this->assertSame('a&MadeUpEntity;b', HtmlEntity::decode('a&MadeUpEntity;b'));
    }

    public function testNoSemicolonStaysLiteral()
    {
        $this->assertSame('a&copy b', HtmlEntity::decode('a&copy b'));
    }

    public function testTooManyDecimalDigitsStaysLiteral()
    {
        $this->assertSame('a&#987654321;b', HtmlEntity::decode('a&#987654321;b'));
    }

    public function testHexLetterAfterAmpStaysLiteral()
    {
        $this->assertSame('a&#abcdef0;b', HtmlEntity::decode('a&#abcdef0;b'));
    }

    public function testEmptyEntityStaysLiteral()
    {
        $this->assertSame('a&#;b', HtmlEntity::decode('a&#;b'));
    }

    public function testMultipleEntitiesInSequence()
    {
        $this->assertSame(
            "#\u{04D2}\u{00A9}",
            HtmlEntity::decode('&#35;&#1234;&copy;')
        );
    }

    public function testNonEntityBytesPassThrough()
    {
        $this->assertSame('plain text without entities', HtmlEntity::decode('plain text without entities'));
    }

    public function testEmptyInput()
    {
        $this->assertSame('', HtmlEntity::decode(''));
    }
}
