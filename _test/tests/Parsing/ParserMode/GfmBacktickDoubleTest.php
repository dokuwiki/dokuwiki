<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmBacktickDouble;

/**
 * Tests for the GFM double-backtick code-span mode.
 *
 * The whole point of this length is being able to embed a lone
 * backtick in inline code — input ``foo`bar`` renders as
 * <code>foo`bar</code>. Combined with the edge-space strip rule,
 * the boundaries can hold backticks too: input `` `foo` ``
 * renders as <code>`foo`</code>.
 */
class GfmBacktickDoubleTest extends ParserTestBase
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

    function testBasicSpan()
    {
        $this->P->addMode('gfm_backtick_double', new GfmBacktickDouble());
        $this->P->parse('foo ``bar`` baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo "]],
            ['monospace_open', []],
            ['unformatted', ['bar']],
            ['monospace_close', []],
            ['cdata', [' baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAllowsInteriorSingleBacktick()
    {
        // GFM example 349. Input ``foo`bar`` — a lone backtick in the
        // body cannot be a valid n=2 closer, so it stays as content.
        $this->P->addMode('gfm_backtick_double', new GfmBacktickDouble());
        $this->P->parse('``foo`bar``');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['monospace_open', []],
            ['unformatted', ['foo`bar']],
            ['monospace_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testStripsEdgeSpaces()
    {
        // GFM example 339. Input `` foo ` bar `` — one leading and one
        // trailing space stripped; the interior lone backtick stays.
        $this->P->addMode('gfm_backtick_double', new GfmBacktickDouble());
        $this->P->parse('`` foo ` bar ``');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['monospace_open', []],
            ['unformatted', ['foo ` bar']],
            ['monospace_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testConvertsNewlinesToSpaces()
    {
        // GFM example 345: newlines inside a span become single spaces,
        // then edge-space stripping applies to the normalized body.
        $this->P->addMode('gfm_backtick_double', new GfmBacktickDouble());
        $this->P->parse("``\nfoo\nbar  \nbaz\n``");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('monospace_open', $modes);

        $unformatted = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'unformatted'
        ));
        $this->assertCount(1, $unformatted);
        $this->assertSame('foo bar   baz', $unformatted[0][1][0]);
    }

    function testAllWhitespaceBodyIsPreserved()
    {
        $this->P->addMode('gfm_backtick_double', new GfmBacktickDouble());
        $this->P->parse('a ``   `` b');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\na "]],
            ['monospace_open', []],
            ['unformatted', ['   ']],
            ['monospace_close', []],
            ['cdata', [' b']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmptyDelimiterDoesNotMatch()
    {
        // A run of four backticks — the length-boundary guards reject it
        // as an n=2 opener followed immediately by an n=2 closer with
        // empty body.
        $this->P->addMode('gfm_backtick_double', new GfmBacktickDouble());
        $this->P->parse('foo ```` bar');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('monospace_open', $modes,
            'Run of 4 backticks must stay literal');
    }

    function testSortValue()
    {
        // Shares the n=1 sort — the length-boundary guards on both modes
        // mean they never compete for the same input anyway.
        $mode = new GfmBacktickDouble();
        $this->assertSame(165, $mode->getSort());
    }
}
