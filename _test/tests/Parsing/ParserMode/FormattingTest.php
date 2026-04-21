<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Deleted;
use dokuwiki\Parsing\ParserMode\Emphasis;
use dokuwiki\Parsing\ParserMode\Internallink;
use dokuwiki\Parsing\ParserMode\Monospace;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Subscript;
use dokuwiki\Parsing\ParserMode\Superscript;
use dokuwiki\Parsing\ParserMode\Underline;

/**
 * Tests for the individual formatting modes (bold, italic, underline, etc.)
 */
class FormattingTest extends ParserTestBase
{
    function testStrong()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse('Foo **Bar** Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['Bar']],
            ['strong_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmphasis()
    {
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse('Foo //Bar// Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['emphasis_open', []],
            ['cdata', ['Bar']],
            ['emphasis_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnderline()
    {
        $this->P->addMode('underline', new Underline());
        $this->P->parse('Foo __Bar__ Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['underline_open', []],
            ['cdata', ['Bar']],
            ['underline_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMonospace()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->parse("Foo ''Bar'' Baz");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['monospace_open', []],
            ['cdata', ['Bar']],
            ['monospace_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSubscript()
    {
        $this->P->addMode('subscript', new Subscript());
        $this->P->parse('Foo <sub>Bar</sub> Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['subscript_open', []],
            ['cdata', ['Bar']],
            ['subscript_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSuperscript()
    {
        $this->P->addMode('superscript', new Superscript());
        $this->P->parse('Foo <sup>Bar</sup> Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['superscript_open', []],
            ['cdata', ['Bar']],
            ['superscript_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testDeleted()
    {
        $this->P->addMode('deleted', new Deleted());
        $this->P->parse('Foo <del>Bar</del> Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['deleted_open', []],
            ['cdata', ['Bar']],
            ['deleted_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNesting()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse('Foo **bold //and italic// text** Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['bold ']],
            ['emphasis_open', []],
            ['cdata', ['and italic']],
            ['emphasis_close', []],
            ['cdata', [' text']],
            ['strong_close', []],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testStrongClosesAfterLink()
    {
        // Regression: `**[[link]]**` must close Strong on the trailing `**`.
        // Strong's exit pattern `(?<=[^\s])\*\*` needs to see the `]` that
        // ends the link as the preceding non-whitespace char. The lexer
        // passes the full subject + offset to PCRE so lookbehinds work
        // across consumed tokens.
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse('**[[wiki:x|link]]** bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['strong_open', []],
            ['internallink', ['wiki:x', 'link']],
            ['strong_close', []],
            ['cdata', [' bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testStrongClosesAfterEmphasis()
    {
        // Regression: `**foo//bar//**` — after emphasis closes, Strong's
        // closing `**` must still match; its lookbehind sees the `/` left
        // behind by the emphasis exit.
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse('**foo//bar//**');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['strong_open', []],
            ['cdata', ['foo']],
            ['emphasis_open', []],
            ['cdata', ['bar']],
            ['emphasis_close', []],
            ['strong_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNoSelfNesting()
    {
        // With flanking-aware Strong: an opener matches only if a valid
        // closer exists (closer preceded by non-whitespace); a closer only
        // fires at `**` preceded by non-whitespace. Here the inner `**`s
        // are adjacent to spaces, so they can't close; the outermost `**`
        // on the right is preceded by `d` and closes the outermost opener.
        // Strong does not re-open inside itself.
        $this->P->addMode('strong', new Strong());
        $this->P->parse('Foo **bold **not nested** end** Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['bold **not nested']],
            ['strong_close', []],
            ['cdata', [' end** Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * @dataProvider provideParagraphBoundaryModes
     *
     * Formatting delimiters must not match across a blank line. An unclosed
     * delimiter followed by a blank line and then an unrelated delimiter
     * further down must stay literal — otherwise the lexer greedily swallows
     * the paragraph break.
     */
    function testDelimitersDoNotSpanParagraphBoundary(
        string $modeName,
        $mode,
        string $input
    ) {
        $this->P->addMode($modeName, $mode);
        $this->P->parse($input);
        foreach ($this->H->calls as $call) {
            $this->assertNotSame(
                $modeName . '_open',
                $call[0],
                "Mode '$modeName' must not open across a blank line in: " . json_encode($input)
            );
        }
    }

    public static function provideParagraphBoundaryModes(): array
    {
        return [
            'strong'      => ['strong',      new Strong(),      "**open\n\nclose**"],
            'emphasis'    => ['emphasis',    new Emphasis(),    "//open\n\nclose//"],
            'underline'   => ['underline',   new Underline(),   "__open\n\nclose__"],
            'monospace'   => ['monospace',   new Monospace(),   "''open\n\nclose''"],
            'subscript'   => ['subscript',   new Subscript(),   "<sub>open\n\nclose</sub>"],
            'superscript' => ['superscript', new Superscript(), "<sup>open\n\nclose</sup>"],
            'deleted'     => ['deleted',     new Deleted(),     "<del>open\n\nclose</del>"],
        ];
    }

    /**
     * A single newline inside a delimiter pair is still valid (multi-line
     * formatting), only blank lines end it.
     */
    function testStrongAllowsSingleNewline()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse("**open\nclose**");
        $this->assertContains(
            'strong_open',
            array_column($this->H->calls, 0),
            'Strong must still match across a single newline'
        );
    }

    /**
     * @dataProvider provideFlankingCases
     *
     * Flanking rules (simplified): an opening delimiter must be followed by
     * a non-whitespace character, and a closing delimiter must be preceded
     * by one. Empty delimiter pairs stay literal.
     */
    function testFlankingRejectsInvalidDelimiters(
        string $modeName,
        $mode,
        string $input
    ) {
        $this->P->addMode($modeName, $mode);
        $this->P->parse($input);
        foreach ($this->H->calls as $call) {
            $this->assertNotSame(
                $modeName . '_open',
                $call[0],
                "Mode '$modeName' must not open in: " . json_encode($input)
            );
        }
    }

    public static function provideFlankingCases(): array
    {
        return [
            // Leading-whitespace opener
            'strong-lead-ws'      => ['strong',      new Strong(),      '** foo bar**'],
            'emphasis-lead-ws'    => ['emphasis',    new Emphasis(),    '// foo bar//'],
            'underline-lead-ws'   => ['underline',   new Underline(),   '__ foo bar__'],
            'monospace-lead-ws'   => ['monospace',   new Monospace(),   "'' foo bar''"],
            'subscript-lead-ws'   => ['subscript',   new Subscript(),   '<sub> foo bar</sub>'],
            'superscript-lead-ws' => ['superscript', new Superscript(), '<sup> foo bar</sup>'],
            'deleted-lead-ws'     => ['deleted',     new Deleted(),     '<del> foo bar</del>'],
            // Trailing-whitespace closer
            'strong-trail-ws'     => ['strong',      new Strong(),      '**foo bar **'],
            'emphasis-trail-ws'   => ['emphasis',    new Emphasis(),    '//foo bar //'],
            'underline-trail-ws'  => ['underline',   new Underline(),   '__foo bar __'],
            'monospace-trail-ws'  => ['monospace',   new Monospace(),   "''foo bar ''"],
            'subscript-trail-ws'  => ['subscript',   new Subscript(),   '<sub>foo bar </sub>'],
            'superscript-trail-ws'=> ['superscript', new Superscript(), '<sup>foo bar </sup>'],
            'deleted-trail-ws'    => ['deleted',     new Deleted(),     '<del>foo bar </del>'],
            // Empty delimiter pairs
            'strong-empty'        => ['strong',      new Strong(),      '**** stays literal'],
            'underline-empty'     => ['underline',   new Underline(),   '____ stays literal'],
            'monospace-empty'     => ['monospace',   new Monospace(),   "'''' stays literal"],
        ];
    }

    /**
     * Single-character bodies still match, they're the smallest valid span.
     */
    function testStrongSingleCharacterBody()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse('**a**');
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_close', array_column($this->H->calls, 0));
    }
}
