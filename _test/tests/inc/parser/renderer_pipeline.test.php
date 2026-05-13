<?php

use DOMWrap\Document;

/**
 * End-to-end test for the parse + render pipeline.
 *
 * Renders the syntax wiki page and inspects the resulting XHTML to catch
 * wholesale regressions in parsing, the call list, or the XHTML renderer.
 */
class renderer_pipeline_test extends DokuWikiTest
{
    public function testWikiSyntaxPageRendersExpectedStructure()
    {
        global $ID;
        $ID = 'wiki:syntax';

        $html = p_wiki_xhtml('wiki:syntax');
        $this->assertNotEmpty($html, 'wiki:syntax must render to non-empty HTML');

        // DOMWrap needs a single root; wrap in a container.
        $doc = (new Document())->html('<div id="root">' . $html . '</div>');

        // Top-level heading from the page source: "====== Formatting Syntax ======"
        $h1 = $doc->find('h1');
        $this->assertSame(1, $h1->count(), 'page must have exactly one h1');
        $this->assertSame('Formatting Syntax', trim($h1->text()));

        // Headers of various levels — page has dozens of sections.
        $this->assertGreaterThan(10, $doc->find('h2')->count(), 'page must have many h2 sections');
        $this->assertGreaterThan(0, $doc->find('h3')->count());
        $this->assertGreaterThan(0, $doc->find('h4')->count());

        // Section edit markers — verifies finalize/Block ran.
        $this->assertGreaterThan(
            5,
            substr_count($html, '<!-- EDIT{'),
            'expected multiple section edit markers'
        );

        // Internal wiki links and external links from the page.
        $this->assertGreaterThan(0, $doc->find('a.wikilink1, a.wikilink2')->count());
        $this->assertGreaterThan(0, $doc->find('a.urlextern')->count());
        $this->assertGreaterThan(0, $doc->find('a.interwiki')->count());

        // Inline formatting.
        $this->assertGreaterThan(0, $doc->find('em')->count());
        $this->assertGreaterThan(0, $doc->find('strong')->count());
        $this->assertGreaterThan(0, $doc->find('code')->count());

        // A table is rendered by the page.
        $this->assertGreaterThan(0, $doc->find('table.inline')->count());

        // Footnote block (the page uses ((...)) footnotes).
        $this->assertSame(1, $doc->find('div.footnotes')->count());

        // The info plugin produces the syntax-plugin list.
        $pluginSection = $doc->find('h2#syntax_plugins')->following('div.level2');
        $this->assertSame(1, $pluginSection->find('ul')->count(), 'info plugin must emit a <ul>');
        $this->assertGreaterThan(
            0,
            $pluginSection->find('ul li.level1 a.urlextern')->count(),
            'info plugin must list at least one syntax plugin entry'
        );
    }
}
