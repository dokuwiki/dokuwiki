<?php

namespace dokuwiki\test\Parsing;

use dokuwiki\Parsing\ModeRegistry;

/**
 * The per-parse syntax override: p_get_instructions($text, $syntax) parses
 * under the requested flavour regardless of the configured $conf['syntax'].
 */
class SyntaxOverrideTest extends \DokuWikiTest
{
    /** Names of all instruction calls produced for $text under $syntax. */
    private function callNames(string $text, ?string $syntax): array
    {
        return array_column(p_get_instructions($text, $syntax), 0);
    }

    public function testDwOverrideUnderMarkdownConfig()
    {
        global $conf;
        $conf['syntax'] = 'md';

        // [[wiki]] is DokuWiki link syntax; it only parses as a link when the
        // DW internallink mode is loaded, which the 'dw' override forces on.
        $names = $this->callNames('[[wiki:syntax]]', 'dw');
        $this->assertContains('internallink', $names);
    }

    public function testMarkdownOverrideUnderDwConfig()
    {
        global $conf;
        $conf['syntax'] = 'dw';

        // *foo* is GFM emphasis; it only fires when gfm_emphasis is loaded,
        // which the 'md' override forces on even though the wiki prefers DW.
        $names = $this->callNames('a *foo* b', 'md');
        $this->assertContains('emphasis_open', $names);
    }

    public function testNullHonoursConfiguredSyntax()
    {
        global $conf;
        $conf['syntax'] = 'md';

        // Passing null means "use the configured syntax" — so [[wiki]] stays
        // literal because DW internallink is not loaded under 'md'.
        $names = $this->callNames('[[wiki:syntax]]', null);
        $this->assertNotContains('internallink', $names);
    }

    public function testTwoRegistriesProduceDifferentModeLists()
    {
        // Catches a singleton regression: building two registries with
        // different syntaxes in one request must yield different mode sets.
        $dw = array_column((new ModeRegistry('dw'))->getModes(), 'mode');
        $md = array_column((new ModeRegistry('md'))->getModes(), 'mode');

        $this->assertNotEquals($dw, $md);
        $this->assertContains('internallink', $dw);
        $this->assertContains('gfm_emphasis', $md);
    }
}
