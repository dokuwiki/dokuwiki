<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Local;
use DokuWikiTest;

/**
 * Tests for the Local class
 *
 * @group plugin_extension
 * @group plugins
 */
class LocalTest extends DokuWikiTest
{

    public function testGetTemplates()
    {
        $local = new Local();
        $templates = $local->getTemplates();

        $this->assertIsArray($templates);
        foreach ($templates as $template) {
            $this->assertInstanceOf(Extension::class, $template);
            $this->assertTrue($template->isTemplate());
        }

        $this->assertArrayHasKey('template:dokuwiki', $templates);
    }

    public function testGetPlugins()
    {
        $local = new Local();
        $plugins = $local->getPlugins();

        $this->assertIsArray($plugins);
        foreach ($plugins as $plugin) {
            $this->assertInstanceOf(Extension::class, $plugin);
            $this->assertFalse($plugin->isTemplate());
        }

        $this->assertArrayHasKey('extension', $plugins);
    }

    public function testGetExtensions()
    {
        $local = new Local();
        $extensions = $local->getExtensions();

        $this->assertIsArray($extensions);
        foreach ($extensions as $extension) {
            $this->assertInstanceOf(Extension::class, $extension);
        }

        $this->assertArrayHasKey('template:dokuwiki', $extensions);
        $this->assertArrayHasKey('extension', $extensions);
    }
}
