<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Extension;
use DokuWikiTest;

/**
 * Tests for the extension plugin
 *
 * @group plugin_extension
 * @group plugins
 */
class ExtensionTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['extension'];

    /**
     * Run checks against the extension plugin itself
     */
    public function testSelf()
    {
        $extension = Extension::createFromDirectory(__DIR__.'/../');

        $this->assertFalse($extension->isTemplate());
        $this->assertEquals('plugin', $extension->getType());
        $this->assertEquals('extension', $extension->getBase());
        $this->assertEquals('extension', $extension->getId());
        $this->assertEquals('`extension`', $extension->getId(true));
        $this->assertEquals(DOKU_INC.'lib/plugins/extension', $extension->getCurrentDir());
        $this->assertEquals(DOKU_INC.'lib/plugins/extension', $extension->getInstallDir());
        $this->assertEquals('Extension Manager', $extension->getDisplayName());
        $this->assertEquals('Andreas Gohr', $extension->getAuthor());
        $this->assertEquals('andi@splitbrain.org', $extension->getEmail());
        $this->assertEquals(md5('andi@splitbrain.org'), $extension->getEmailID());
        $this->assertStringContainsString('plugins', $extension->getDescription());
        $this->assertEquals('https://www.dokuwiki.org/plugin:extension', $extension->getURL());
        $this->assertMatchesRegularExpression('/\d\d\d\d-\d\d-\d\d/',$extension->getInstalledVersion());
        $this->assertContains('Admin', $extension->getComponentTypes());
        $this->assertIsArray($extension->getDependencyList());
        $this->assertEmpty($extension->getDependencyList());
        $this->assertEmpty($extension->getMinimumPHPVersion());
        $this->assertEmpty($extension->getMaximumPHPVersion());
        $this->assertTrue($extension->isInstalled());
        $this->assertFalse($extension->isGitControlled());
        $this->assertTrue($extension->isBundled());
        $this->assertFalse($extension->isProtected());
        $this->assertFalse($extension->isInWrongFolder());
        $this->assertTrue($extension->isEnabled());
        $this->assertFalse($extension->hasChangedURL());
        $this->assertFalse($extension->isUpdateAvailable());
    }

    /**
     * Run checks against the dokuwiki template
     */
    public function testDokuWikiTemplate()
    {
        $extension = Extension::createFromDirectory(__DIR__.'/../../../tpl/dokuwiki/');

        $this->assertTrue($extension->isTemplate());
        $this->assertEquals('template', $extension->getType());
        $this->assertEquals('dokuwiki', $extension->getBase());
        $this->assertEquals('template:dokuwiki', $extension->getId());
        $this->assertEquals('`template:dokuwiki`', $extension->getId(true));
        $this->assertEquals(DOKU_INC.'lib/tpl/dokuwiki', $extension->getCurrentDir());
        $this->assertEquals(DOKU_INC.'lib/tpl/dokuwiki', $extension->getInstallDir());
        $this->assertEquals('DokuWiki Template', $extension->getDisplayName());
        $this->assertEquals('Anika Henke', $extension->getAuthor());
        $this->assertEquals('anika@selfthinker.org', $extension->getEmail());
        $this->assertEquals(md5('anika@selfthinker.org'), $extension->getEmailID());
        $this->assertStringContainsString('default template', $extension->getDescription());
        $this->assertEquals('https://www.dokuwiki.org/template:dokuwiki', $extension->getURL());
        $this->assertMatchesRegularExpression('/\d\d\d\d-\d\d-\d\d/',$extension->getInstalledVersion());
        $this->assertContains('Template', $extension->getComponentTypes());
        $this->assertIsArray($extension->getDependencyList());
        $this->assertEmpty($extension->getDependencyList());
        $this->assertEmpty($extension->getMinimumPHPVersion());
        $this->assertEmpty($extension->getMaximumPHPVersion());
        $this->assertTrue($extension->isInstalled());
        $this->assertFalse($extension->isGitControlled());
        $this->assertTrue($extension->isBundled());
        $this->assertTrue($extension->isProtected()); // protected because it's the current template
        $this->assertFalse($extension->isInWrongFolder());
        $this->assertTrue($extension->isEnabled());
        $this->assertFalse($extension->hasChangedURL());
        $this->assertFalse($extension->isUpdateAvailable());
    }
}
