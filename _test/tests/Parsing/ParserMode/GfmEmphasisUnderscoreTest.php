<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmEmphasisUnderscore;

/**
 * Tests for the GFM underscore emphasis mode (`_text_`).
 */
class GfmEmphasisUnderscoreTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $conf['syntax'] = 'markdown';
        ModeRegistry::reset();
    }

    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    function testBasicUnderscore()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('Foo _Bar_ Baz');
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

    function testSingleCharacter()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('foo _b_ bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo "]],
            ['emphasis_open', []],
            ['cdata', ['b']],
            ['emphasis_close', []],
            ['cdata', [' bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleWords()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('_one two three_');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['one two three']],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testIntrawordUnderscoreIsNotEmphasised()
    {
        // GFM's key word-boundary rule: underscores inside words stay literal.
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('this_is_not_an_emphasis');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nthis_is_not_an_emphasis"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testOpenerFollowedBySpaceDoesNotEmphasise()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('foo _ bar_ baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo _ bar_ baz"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testDoubleUnderscoreDoesNotEmphasise()
    {
        // `__foo__` must stay literal. At the first `_`, the lookahead
        // `(?=[^\s_])` forbids entry (next char is another `_`). At the
        // second `_`, the lookbehind also fails because `_` itself is not
        // a "non-word" character (it's excluded from NON_WORD_CHAR so that
        // `__foo` can't open emphasis at the inner underscore).
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('foo __bar__ baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo __bar__ baz"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTwoSeparateEmphasisOnOneLine()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('_one_ and _two_');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['one']],
            ['emphasis_close', []],
            ['cdata', [' and ']],
            ['emphasis_open', []],
            ['cdata', ['two']],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultilineEmphasis()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse("_line\nline\nline_");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ["line\nline\nline"]],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSortValue()
    {
        $mode = new GfmEmphasisUnderscore();
        $this->assertSame(80, $mode->getSort());
    }

    function testDoesNotSpanParagraphBoundary()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse("_open\n\nclose_");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('emphasis_open', $modes,
            'GfmEmphasisUnderscore must not open when the closing `_` is past a blank line');
    }

    function testAllowsSingleNewlineInsideMultiline()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse("_open\nclose_");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('emphasis_open', $modes,
            'GfmEmphasisUnderscore must still match across a single newline');
    }

    /**
     * The intraword rule must apply to multibyte letters, not just ASCII.
     * This test is derived from CommonMark spec §6.2 example 418:
     *
     *     пристаням_стремятся_
     *
     * which must render as literal (no emphasis). The surrounding Cyrillic
     * letters are word-like; the underscores are intraword and must not
     * emphasize.
     *
     * The word-boundary constants (NO_WORD_BEFORE / NO_WORD_AFTER) are
     * defined positively (matching explicit non-word chars) rather than
     * negatively (not matching a-zA-Z0-9), so multibyte UTF-8 bytes — which
     * are not in any ASCII class — are correctly treated as word-like.
     *
     * @dataProvider provideMultibyteIntrawordCases
     */
    function testIntrawordUnderscoreInMultibyteText(string $input)
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse($input);
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains(
            'emphasis_open',
            $modes,
            "Intraword `_` in multibyte text must not emphasize: " . json_encode($input)
        );
    }

    public static function provideMultibyteIntrawordCases(): array
    {
        return [
            // CommonMark spec §6.2 ex. 418 — Cyrillic intraword
            'cyrillic-trailing'  => ['пристаням_стремятся_'],
            // CommonMark spec §6.2 ex. 420 — Cyrillic leading
            'cyrillic-leading'   => ['_пристаням_стремятся'],
            // German umlaut — no established spec example, but the expected
            // behavior is uncontroversial: intraword `_` stays literal.
            'german-umlaut'      => ['für_etwas_text'],
            // CJK — same expectation
            'cjk-intraword'      => ['日本_語_の'],
            // Greek
            'greek-intraword'    => ['αυτό_είναι_κείμενο'],
        ];
    }

    /**
     * A `_foo_` span surrounded by multibyte letters must NOT open at the
     * first `_` (it would be intraword) AND must still NOT open if the
     * following letters are multibyte. Verifies that both the lookbehind
     * and the closing-delimiter lookahead reject multibyte word chars.
     */
    function testMultibyteWordCharsAreNotTreatedAsBoundary()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        // Intraword between Cyrillic on the left and Cyrillic on the right.
        $this->P->parse('до_середины_текста');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('emphasis_open', $modes,
            'Cyrillic-surrounded `_` must not emphasize');
    }

    /**
     * Positive: when the surrounding non-word context is whitespace or
     * punctuation, multibyte content *inside* the emphasis span is fine.
     * `_für etwas_` surrounded by spaces should emphasize the multibyte text.
     */
    function testMultibyteContentInsideEmphasisWorks()
    {
        $this->P->addMode('gfm_emphasis_underscore', new GfmEmphasisUnderscore());
        $this->P->parse('foo _für etwas_ bar');
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('emphasis_open', $modes,
            'Multibyte text inside `_..._` must emphasize when boundaries are clear');
        $this->assertContains('emphasis_close', $modes,
            'Multibyte text inside `_..._` must emphasize when boundaries are clear');
    }
}
