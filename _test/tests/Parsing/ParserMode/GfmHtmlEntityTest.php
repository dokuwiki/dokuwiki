<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmHtmlEntity;

/**
 * Sanity tests for the GfmHtmlEntity lexer mode.
 *
 * Decoding semantics are tested exhaustively in
 * {@see \dokuwiki\test\Parsing\Helpers\HtmlEntityTest}; this class
 * covers only that the mode wires up to the lexer correctly and emits
 * cdata at the right positions. Consecutive cdata calls are coalesced
 * by Handler\Block::addCall during finalize(), so a successful match
 * shows up as a single cdata containing the decoded character spliced
 * into the surrounding text.
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

    public function testNumericDecodes()
    {
        $this->assertParsedCdata('x &#35; y', "\nx # y");
    }

    public function testNamedDecodes()
    {
        $this->assertParsedCdata('a&copy;b', "\na\u{00A9}b");
    }

    public function testUnknownNameStaysLiteral()
    {
        $this->assertParsedCdata('a&MadeUpEntity;b', "\na&MadeUpEntity;b");
    }

    public function testNonMatchingInputStaysLiteral()
    {
        $this->assertParsedCdata('a&#abcdef0;b', "\na&#abcdef0;b");
    }
}
