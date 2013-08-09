<?php
/**
 * @group plugin_extension
 */
class helper_plugin_extension_extension_test extends DokuWikiTest {

     protected $pluginsEnabled = array('extension');

    /**
     * FIXME should we test this without internet first?
     *
     * @group internet
     */
    public function testExtensionParameters() {

        $extension = new helper_plugin_extension_extension();

        $extension->setExtension('extension');
        $this->assertEquals('extension', $extension->getID());
        $this->assertEquals('extension', $extension->getBase());
        $this->assertEquals('Extension Manager', $extension->getDisplayName());
        $this->assertEquals('Michael Hamann', $extension->getAuthor());
        $this->assertEquals('michael@content-space.de', $extension->getEmail());
        $this->assertEquals(md5('michael@content-space.de'), $extension->getEmailID());
        $this->assertEquals('https://www.dokuwiki.org/plugin:extension', $extension->getURL());
        $this->assertEquals('Allows managing and installing plugins and templates', $extension->getDescription());
        $this->assertFalse($extension->isTemplate());
        $this->assertTrue($extension->isEnabled());
        $this->assertTrue($extension->isInstalled());
        $this->assertTrue($extension->isBundled());

        $extension->setExtension('testing');
        $this->assertEquals('testing', $extension->getID());
        $this->assertEquals('testing', $extension->getBase());
        $this->assertEquals('Testing Plugin', $extension->getDisplayName());
        $this->assertEquals('Tobias Sarnowski', $extension->getAuthor());
        $this->assertEquals('tobias@trustedco.de', $extension->getEmail());
        $this->assertEquals(md5('tobias@trustedco.de'), $extension->getEmailID());
        $this->assertEquals('http://www.dokuwiki.org/plugin:testing', $extension->getURL());
        $this->assertEquals('Used to test the test framework. Should always be disabled.', $extension->getDescription());
        $this->assertFalse($extension->isTemplate());
        $this->assertFalse($extension->isEnabled());
        $this->assertTrue($extension->isInstalled());
        $this->assertTrue($extension->isBundled());

        $extension->setExtension('template:dokuwiki');
        $this->assertEquals('template:dokuwiki', $extension->getID());
        $this->assertEquals('dokuwiki', $extension->getBase());
        $this->assertEquals('DokuWiki Template', $extension->getDisplayName());
        $this->assertEquals('Anika Henke', $extension->getAuthor());
        $this->assertEquals('anika@selfthinker.org', $extension->getEmail());
        $this->assertEquals(md5('anika@selfthinker.org'), $extension->getEmailID());
        $this->assertEquals('http://www.dokuwiki.org/template:dokuwiki', $extension->getURL());
        $this->assertEquals('DokuWiki\'s default template since 2012', $extension->getDescription());
        $this->assertTrue($extension->isTemplate());
        $this->assertTrue($extension->isEnabled());
        $this->assertTrue($extension->isInstalled());
        $this->assertTrue($extension->isBundled());

    }

}