<?php

/**
 * Class mock_helper_plugin_extension_extension
 *
 * makes protected methods accessible
 */
class mock_helper_plugin_extension_extension extends helper_plugin_extension_extension {
    public function find_folders(&$result, $base, $default_type = 'plugin', $dir = '') {
        return parent::find_folders($result, $base, $default_type, $dir);
    }

}

/**
 * @group plugin_extension
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
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

    public function testFindFoldersPlugins() {
        $extension = new mock_helper_plugin_extension_extension();
        $tdir      = dirname(__FILE__).'/testdata';

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/plugin1", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('plugin', $result['old'][0]['type']);
        $this->assertEquals('plugin1', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/plugin2", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('plugin', $result['new'][0]['type']);
        $this->assertEquals('plugin2', $result['new'][0]['base']);
        $this->assertEquals('plugin2', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/plgsub3", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('plugin', $result['old'][0]['type']);
        $this->assertEquals('plgsub3/plugin3', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/plgsub4", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('plugin', $result['new'][0]['type']);
        $this->assertEquals('plugin4', $result['new'][0]['base']);
        $this->assertEquals('plgsub4/plugin4', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/plgfoo5", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('plugin', $result['new'][0]['type']);
        $this->assertEquals('plugin5', $result['new'][0]['base']);
        $this->assertEquals('plgfoo5', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/plgsub6/plgfoo6", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('plugin', $result['new'][0]['type']);
        $this->assertEquals('plugin6', $result['new'][0]['base']);
        $this->assertEquals('plgsub6/plgfoo6', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/either1", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('plugin', $result['old'][0]['type']);
        $this->assertEquals('either1', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/eithersub2/either2", 'plugin');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('plugin', $result['old'][0]['type']);
        $this->assertEquals('eithersub2/either2', $this->extdir($result['old'][0]['tmp']));
    }

    public function testFindFoldersTemplates() {
        $extension = new mock_helper_plugin_extension_extension();
        $tdir      = dirname(__FILE__).'/testdata';

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/template1", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('template', $result['old'][0]['type']);
        $this->assertEquals('template1', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/template2", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template2', $result['new'][0]['base']);
        $this->assertEquals('template2', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplsub3", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('template', $result['old'][0]['type']);
        $this->assertEquals('tplsub3/template3', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplsub4", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template4', $result['new'][0]['base']);
        $this->assertEquals('tplsub4/template4', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplfoo5", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template5', $result['new'][0]['base']);
        $this->assertEquals('tplfoo5', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplsub6/tplfoo6", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template6', $result['new'][0]['base']);
        $this->assertEquals('tplsub6/tplfoo6', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/either1", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('template', $result['old'][0]['type']);
        $this->assertEquals('either1', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/eithersub2/either2", 'template');
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('template', $result['old'][0]['type']);
        $this->assertEquals('eithersub2/either2', $this->extdir($result['old'][0]['tmp']));
    }

    public function testFindFoldersTemplatesAutodetect() {
        $extension = new mock_helper_plugin_extension_extension();
        $tdir      = dirname(__FILE__).'/testdata';

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/template1");
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('template', $result['old'][0]['type']);
        $this->assertEquals('template1', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/template2");
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template2', $result['new'][0]['base']);
        $this->assertEquals('template2', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplsub3");
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('template', $result['old'][0]['type']);
        $this->assertEquals('tplsub3/template3', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplsub4");
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template4', $result['new'][0]['base']);
        $this->assertEquals('tplsub4/template4', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplfoo5");
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template5', $result['new'][0]['base']);
        $this->assertEquals('tplfoo5', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/tplsub6/tplfoo6");
        $this->assertTrue($ok);
        $this->assertEquals(1, count($result['new']));
        $this->assertEquals('template', $result['new'][0]['type']);
        $this->assertEquals('template6', $result['new'][0]['base']);
        $this->assertEquals('tplsub6/tplfoo6', $this->extdir($result['new'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/either1");
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('plugin', $result['old'][0]['type']);
        $this->assertEquals('either1', $this->extdir($result['old'][0]['tmp']));

        $result = array('old' => array(), 'new' => array());
        $ok     = $extension->find_folders($result, "$tdir/eithersub2/either2");
        $this->assertTrue($ok);
        $this->assertEquals(0, count($result['new']));
        $this->assertEquals(1, count($result['old']));
        $this->assertEquals('plugin', $result['old'][0]['type']);
        $this->assertEquals('eithersub2/either2', $this->extdir($result['old'][0]['tmp']));
    }

    /**
     * remove the test data directory from a dir name for cross install comparison
     *
     * @param string $dir
     * @return string
     */
    protected function extdir($dir) {
        $tdir = dirname(__FILE__).'/testdata';
        $len  = strlen($tdir);
        $dir  = trim(substr($dir, $len), '/');
        return $dir;
    }
}