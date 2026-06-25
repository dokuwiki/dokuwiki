<?php

namespace dokuwiki\test\Parsing;

/**
 * Locale files are core assets authored in DokuWiki syntax. They must render
 * as 'dw' even when the wiki is configured for Markdown — otherwise bracket
 * and apostrophe pairs would survive into the output as literal text.
 */
class LocaleXhtmlTest extends \DokuWikiTest
{
    /** Stage a locale file under the (temporary) DOKU_CONF lang dir. */
    private function stageLocale(string $id, string $content): void
    {
        global $conf;
        $dir = DOKU_CONF . 'lang/' . $conf['lang'];
        io_makeFileDir($dir . '/' . $id . '.txt');
        io_saveFile($dir . '/' . $id . '.txt', $content);
    }

    public function testLocaleRendersAsDwUnderMarkdownConfig()
    {
        global $conf;
        $conf['syntax'] = 'md';

        $this->stageLocale('synctest', "A [[wiki:syntax]] link.");

        $html = p_locale_xhtml('synctest');

        // Rendered as DW: the bracket pair becomes a real link, not literal text
        $this->assertStringContainsString('<a ', $html, 'locale link must render as a link under md config');
        $this->assertStringNotContainsString('[[wiki:syntax]]', $html, 'bracket pair must not survive as literal text');
    }
}
