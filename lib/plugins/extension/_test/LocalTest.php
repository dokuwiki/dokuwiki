<?php

namespace easywiki\plugin\extension\test;

use easywiki\plugin\extension\Extension;
use easywiki\plugin\extension\Local;
use EasyWikiTest;

/**
 * Tests for the Local class
 *
 * @group plugin_extension
 * @group plugins
 */
class LocalTest extends EasyWikiTest
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

        $this->assertArrayHasKey('template:easywiki', $templates);
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

        $this->assertArrayHasKey('template:easywiki', $extensions);
        $this->assertArrayHasKey('extension', $extensions);
    }
}
