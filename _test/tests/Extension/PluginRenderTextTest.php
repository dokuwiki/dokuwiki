<?php

namespace dokuwiki\test\Extension;

use dokuwiki\Extension\PluginTrait;

/**
 * PluginTrait::render_text() defaults to rendering DokuWiki syntax, because
 * its historical callers pass DW-syntax strings. Passing null instead honors
 * the configured wiki syntax — for rendering user content.
 */
class PluginRenderTextTest extends \DokuWikiTest
{
    /** A bare object carrying the plugin trait, enough to call render_text(). */
    private function plugin(): object
    {
        return new class {
            use PluginTrait;
        };
    }

    public function testDefaultRendersAsDwEvenUnderMarkdownConfig()
    {
        global $conf;
        $conf['syntax'] = 'md';

        // default $syntax = 'dw' wins, so [[a]] becomes a real link
        $html = $this->plugin()->render_text('[[a]]');
        $this->assertStringContainsString('<a ', $html);
        $this->assertStringNotContainsString('[[a]]', $html);
    }

    public function testNullHonoursConfiguredSyntax()
    {
        global $conf;
        $conf['syntax'] = 'md';

        // passing null honors the configured 'md' syntax, where DW internallink
        // is not loaded, so [[a]] survives as literal text
        $html = $this->plugin()->render_text('[[a]]', 'xhtml', null);
        $this->assertStringContainsString('[[a]]', $html);
    }
}
