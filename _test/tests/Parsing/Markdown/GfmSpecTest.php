<?php

namespace dokuwiki\test\Parsing\Markdown;

use dokuwiki\Parsing\ModeRegistry;

/**
 * Roundtrip tests driven by GFM's spec.txt.
 *
 * Each example in gfm-spec/spec.txt becomes one data-provider case. The
 * markdown input is run through DokuWiki's full pipeline (parser + XHTML
 * renderer) and the result is compared to the expected HTML from the spec,
 * tolerating whitespace differences around block-level tags.
 *
 * Most examples are expected to FAIL until the relevant GFM parser modes
 * are implemented — they are the branch's living TODO list for GFM parity.
 * Do not mark such failures incomplete or skipped.
 *
 * `gfm-spec/skip.php` lists examples that are deliberately out of scope
 * for DokuWiki (e.g. CommonMark flanking-delimiter edge cases). Those are
 * reported as skipped with a reason.
 */
class GfmSpecTest extends \DokuWikiTest
{
    private const FIXTURE_DIR = __DIR__ . '/gfm-spec/';

    public static function specProvider(): iterable
    {
        $reader = new SpecReader(self::FIXTURE_DIR . 'spec.txt');
        $skip   = require self::FIXTURE_DIR . 'skip.php';

        // Spec convention (spec.txt §"About this document"): the `→`
        // character in examples represents a literal tab. Restore the
        // tab in both input and expected output so the corpus exercises
        // real tab-handling behavior, not arrow-character handling.
        foreach ($reader->examples() as $ex) {
            $reason = $skip[$ex['number']] ?? null;
            $label  = sprintf('#%d %s', $ex['number'], $ex['section']);
            $md     = strtr($ex['markdown'], ["\u{2192}" => "\t"]);
            $html   = strtr($ex['html'], ["\u{2192}" => "\t"]);
            yield $label => [$md, $html, $reason];
        }
    }

    /**
     * @dataProvider specProvider
     */
    public function testExample(string $md, string $expected, ?string $skipReason): void
    {
        if ($skipReason !== null) {
            $this->markTestSkipped($skipReason);
        }
        $actual = $this->renderMarkdown($md);
        $this->assertHtmlEquals($expected, $actual);
    }

    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    /**
     * Render markdown text through DokuWiki's full parser pipeline under
     * the `md` syntax setting, using {@see SpecCompatRenderer} —
     * an XHTML renderer subclass that emits the minimal link/media HTML
     * shape the GFM spec expects. Production rendering is unchanged;
     * this override exists so spec output can be compared byte-for-byte.
     *
     * Typography is forced off for the spec run: $conf[typography] = 0
     * keeps the Quotes and MultiplyEntity modes (curly quote pairing,
     * apostrophe to numeric entity) out of the mode list. Both are
     * correct for production wiki prose but diverge byte-for-byte from
     * spec output. SpecCompatRenderer additionally neutralizes the
     * Entity-table substitutions (--, ---, ->, (c), ...) at render time;
     * see SpecCompatRenderer::entity().
     */
    private function renderMarkdown(string $text): string
    {
        global $conf;
        $conf['syntax'] = 'md';
        $conf['typography'] = 0;
        ModeRegistry::reset();

        $instructions = p_get_instructions($text);

        $renderer = new SpecCompatRenderer();
        $renderer->reset();
        $renderer->smileys   = getSmileys();
        $renderer->entities  = getEntities();
        $renderer->acronyms  = getAcronyms();
        $renderer->interwiki = getInterwiki();

        foreach ($instructions as $instruction) {
            if (method_exists($renderer, $instruction[0])) {
                call_user_func_array([$renderer, $instruction[0]], $instruction[1] ?: []);
            }
        }
        return $renderer->doc;
    }

    /**
     * Assert two HTML strings are equivalent after whitespace normalization.
     *
     * DokuWiki's XHTML renderer emits extra whitespace around block tags
     * that the spec's reference HTML omits. The comparator strips whitespace
     * only around **block-level** tags (p, div, h1-h6, ul/ol/li, table/tr/td,
     * blockquote, pre, hr). Whitespace around **inline** tags (em, strong,
     * a, code, span, img, br, etc.) is preserved, because `<em>x</em> y`
     * and `<em>x</em>y` render differently.
     */
    private function assertHtmlEquals(string $expected, string $actual): void
    {
        $this->assertEquals(
            $this->normalizeHtml($expected),
            $this->normalizeHtml($actual)
        );
    }

    /**
     * Strip whitespace adjacent to block-level tags; leave inline tags alone.
     *
     * Additionally drops DokuWiki-specific heading decoration that carries no
     * semantic meaning for GFM-conformance checks:
     *
     * - `<div class="levelN">` / matching `</div>` section wrappers the
     *   renderer emits after every header call.
     * - `class="..."` / `id="..."` attributes on h1-h6 (section-edit anchor
     *   and header-id generation; fine to ignore, the spec output has none).
     */
    private function normalizeHtml(string $html): string
    {
        $block = 'p|div|h[1-6]|hr|ul|ol|li|blockquote|pre|table|thead|tbody|tfoot|tr|th|td';

        // Drop DokuWiki's `<div class="levelN">` section wrappers and the
        // HTML comments (`<!-- EDIT... -->`) its section-edit machinery
        // inserts after each heading. Neither is semantically part of the
        // heading and GFM reference output never contains them.
        $html = preg_replace('#<div class="level[1-6]">\s*#', '', $html);
        $html = preg_replace('#\s*</div>\s*#', '', $html);
        $html = preg_replace('#<!--[^<]*?-->#', '', $html);

        // Strip sectionedit/id decoration from headings.
        $html = preg_replace('#<(h[1-6])(?:\s+(?:class|id)="[^"]*")+\s*>#', '<$1>', $html);

        // Whitespace before/after an opening block tag (including attributes)
        $html = preg_replace('#\s*<(' . $block . ')((?:\s[^>]*)?)>\s*#', '<$1$2>', $html);
        // Whitespace before/after a closing block tag
        $html = preg_replace('#\s*</(' . $block . ')>\s*#', '</$1>', $html);

        return trim($html);
    }
}
