<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Deleted;
use dokuwiki\Parsing\ParserMode\Emphasis;
use dokuwiki\Parsing\ParserMode\Externallink;
use dokuwiki\Parsing\ParserMode\Footnote;
use dokuwiki\Parsing\ParserMode\Internallink;
use dokuwiki\Parsing\ParserMode\Monospace;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Subscript;
use dokuwiki\Parsing\ParserMode\Superscript;
use dokuwiki\Parsing\ParserMode\Underline;
use dokuwiki\Parsing\ParserMode\Unformatted;

/**
 * Tests for the individual formatting modes (bold, italic, underline, etc.)
 */
class FormattingTest extends ParserTestBase
{
    public function testStrong()
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

    public function testEmphasis()
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

    public function testUnderline()
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

    public function testMonospace()
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

    public function testSubscript()
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

    public function testSuperscript()
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

    public function testDeleted()
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

    public function testNesting()
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

    public function testStrongClosesAfterLink()
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

    public function testStrongClosesAfterEmphasis()
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

    public function testNoSelfNesting()
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
    public function testDelimitersDoNotSpanParagraphBoundary(
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
    public function testStrongAllowsSingleNewline()
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
    public function testFlankingRejectsInvalidDelimiters(
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
    public function testStrongSingleCharacterBody()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse('**a**');
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_close', array_column($this->H->calls, 0));
    }

    /**
     * An opener without a valid closer must stay literal while a later
     * valid span in the same paragraph still matches. The closer scan
     * rejects the first candidate (its only potential closer is preceded
     * by whitespace, or is the very opener of the valid span) without
     * blocking the second.
     *
     * @dataProvider provideRejectedOpenerBeforeValidSpan
     */
    public function testRejectedOpenerBeforeValidSpanStaysLiteral(
        string $modeName,
        $mode,
        string $input,
        string $openInstruction
    ) {
        $this->P->addMode($modeName, $mode);
        $this->P->parse($input);

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains([$openInstruction, []], $calls);
        $this->assertContains(['cdata', ['bar']], $calls);
        $this->assertStringContainsString(' foo ', $this->H->calls[2][1][0]);
    }

    public static function provideRejectedOpenerBeforeValidSpan(): array
    {
        return [
            'strong'      => ['strong',      new Strong(),      '** foo **bar**',          'strong_open'],
            'emphasis'    => ['emphasis',    new Emphasis(),    '// foo //bar//',          'emphasis_open'],
            'underline'   => ['underline',   new Underline(),   '__ foo __bar__',          'underline_open'],
            'monospace'   => ['monospace',   new Monospace(),   "'' foo ''bar''",          'monospace_open'],
            'deleted'     => ['deleted',     new Deleted(),     '<del> foo <del>bar</del>', 'deleted_open'],
            'subscript'   => ['subscript',   new Subscript(),   '<sub> foo <sub>bar</sub>', 'subscript_open'],
            'superscript' => ['superscript', new Superscript(), '<sup> foo <sup>bar</sup>', 'superscript_open'],
        ];
    }

    /**
     * A closer in the next paragraph must not validate an opener in the
     * current one, and the paragraph after a rejected opener must parse
     * normally (the closer-free memo range ends at the paragraph break).
     */
    public function testRejectedOpenerDoesNotAffectNextParagraph()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse("**no closer here\n\n**closed** here");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['cdata', ["\n**no closer here\n\n"]], $calls);
        $this->assertContains(['strong_open', []], $calls);
        $this->assertContains(['cdata', ['closed']], $calls);
    }

    /**
     * There is no length limit on a formatting span within a paragraph:
     * real pages contain styled spans well beyond 32KB, which must not
     * degrade to literal text. This is what caps the closer scan cannot
     * provide (a PCRE counted repeat maxes out at 65535) and why the scan
     * runs as a memoized lexer closer pattern instead.
     */
    public function testVeryLongSpanIsRecognized()
    {
        $body = trim(str_repeat('some words ', 7000)); // ~77KB
        $this->P->addMode('strong', new Strong());
        $this->P->parse('**' . $body . '**');

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['strong_open', []], $calls);
        $this->assertContains(['cdata', [$body]], $calls);
    }

    /**
     * Delimiter-dense input without any closer must stay literal — and,
     * although a test can't assert wall clock, it exercises the memoized
     * closer-free range that keeps this shape linear instead of scanning
     * the paragraph once per opener.
     */
    public function testManyOpenersWithoutCloserStayLiteral()
    {
        $text = trim(str_repeat('**a ', 500));
        $this->P->addMode('strong', new Strong());
        $this->P->parse($text);

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['strong_open', []], $calls);
        $this->assertContains(['cdata', ["\n" . $text]], $calls);
    }

    /**
     * An inner delimiter must not pair with one in a following sibling span:
     * its closer lies beyond the enclosing mode's closer, so it can never
     * close within the parent and must stay literal. Here the `//` inside the
     * first `''…''` would otherwise reach the `//` of the second, dragging the
     * monospace boundary along with it.
     */
    public function testInnerDelimiterDoesNotSpanEnclosingCloser()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse("''a//b'', ''c//d''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['emphasis_open', []], $calls);
        $this->assertContains(['cdata', ['a//b']], $calls);
        $this->assertContains(['cdata', ['c//d']], $calls);
    }

    /**
     * The counterpart to the above: an inner delimiter whose closer does sit
     * inside the enclosing span still nests normally.
     */
    public function testInnerDelimiterClosingWithinEnclosingSpanNests()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse("''a//b//c''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['monospace_open', []], $calls);
        $this->assertContains(['emphasis_open', []], $calls);
        $this->assertContains(['cdata', ['b']], $calls);
    }

    /**
     * The enclosing closer may directly follow the inner opener. The `//`
     * in the first `''…''` sits right before the closing `''`, so it can
     * only pair with the `//` of the second span — it must stay literal
     * even though no character separates it from the monospace closer.
     */
    public function testInnerDelimiterAdjacentToEnclosingCloserStaysLiteral()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse("''//'', ''a c//d''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['emphasis_open', []], $calls);
        $this->assertContains(['cdata', ['//']], $calls);
        $this->assertContains(['cdata', ['a c//d']], $calls);
    }

    /**
     * A closer lookalike inside protected content does not count against a
     * nested delimiter: the `''` inside the nowiki span never closes the
     * monospace mode, so the emphasis pair around it still nests.
     */
    public function testFakeEnclosingCloserInProtectedContentStillNests()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('unformatted', new Unformatted());
        $this->P->parse("''a //b <nowiki>x''y</nowiki> c// d''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['emphasis_open', []], $calls);
        $this->assertContains(['unformatted', ["x''y"]], $calls);
        $this->assertContains(['cdata', [' d']], $calls);
    }

    /**
     * A closer lookalike inside protected content does not validate an
     * opener either: the only `''` after the candidate lies in a nowiki
     * span the lexer will consume verbatim, so monospace never opens.
     */
    public function testFakeCloserInProtectedContentDoesNotOpenFormatting()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('unformatted', new Unformatted());
        $this->P->parse("x ''a <nowiki>b''</nowiki> c");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['monospace_open', []], $calls);
        $this->assertContains(['unformatted', ["b''"]], $calls);
    }

    /**
     * A guarded formatting mode may enclose an unguarded mode (a footnote)
     * that itself contains formatting. The enclosing-closer check must look
     * past the unguarded footnote to the strong span: the inner // has its
     * only closer beyond the strong closer, so it stays literal instead of
     * opening inside the footnote and pairing across the footnote and strong
     * boundaries (which left strong unclosed).
     */
    public function testFormattingInsideFootnoteDoesNotPairAcrossEnclosingStrong()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('footnote', new Footnote());
        $this->P->parse('**b ((n //x)) c** and //y//');

        $names = array_column($this->flattenCalls($this->H->calls), 0);
        $counts = array_count_values($names);

        // emphasis opens once, for the trailing //y// only, not inside the footnote
        $this->assertSame(1, $counts['emphasis_open'] ?? 0);
        // strong is closed properly rather than left dangling
        $this->assertArrayHasKey('strong_close', $counts);
    }

    /**
     * A // preceded by a colon is a valid emphasis closer, so the span in
     * //identifier:// closes at that delimiter and the following **label**
     * is emitted as strong.
     */
    public function testEmphasisClosesAtColonPrecededDelimiter()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('//identifier:// **label**');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['identifier:']],
            ['emphasis_close', []],
            ['cdata', [' ']],
            ['strong_open', []],
            ['cdata', ['label']],
            ['strong_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * A recognized URL scheme is consumed as a link token whose content the
     * closer scan never looks into, so the // in http:// cannot close the
     * span. Emphasis wraps the whole run and closes at the trailing //.
     */
    public function testEmphasisWrapsRecognizedUrl()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('//italic known url http://foo.com/bar is here//');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['italic known url ']],
            ['externallink', ['http://foo.com/bar', null]],
            ['cdata', [' is here']],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * An unrecognized scheme is plain text and gets no special guarding: the
     * // in proto:// closes emphasis like any other delimiter, and the text
     * past that closer stays literal.
     */
    public function testUnrecognizedSchemeClosesEmphasis()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('//italic unknown url proto://foo.com/bar is here//');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['italic unknown url proto:']],
            ['emphasis_close', []],
            ['cdata', ['foo.com/bar is here//']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * A recognized URL greedily consumes the trailing //, so no closer
     * remains before the paragraph break. The opener therefore stays literal
     * rather than opening a span that could never close.
     */
    public function testRecognizedUrlEatingTrailingCloserLeavesOpenerLiteral()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('//italic known url http://foo.com/bar//');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n//italic known url "]],
            ['externallink', ['http://foo.com/bar//', null]],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * An unrecognized scheme's // closes emphasis at proto://; the trailing
     * // has nothing after it to open a new span and stays literal.
     */
    public function testUnrecognizedSchemeClosesEmphasisWithLiteralTail()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('//italic unknown url proto://foo.com/bar//');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['italic unknown url proto:']],
            ['emphasis_close', []],
            ['cdata', ['foo.com/bar//']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    /**
     * Flatten a handler call list, inlining the calls a footnote buffers in
     * its nest rewrite, into a list of [name, args] pairs.
     *
     * @param array $calls
     * @return array[]
     */
    private function flattenCalls(array $calls): array
    {
        $out = [];
        foreach ($calls as $call) {
            if ($call[0] === 'nest') {
                $out = array_merge($out, $this->flattenCalls($call[1][0]));
            } else {
                $out[] = [$call[0], $call[1]];
            }
        }
        return $out;
    }
}
