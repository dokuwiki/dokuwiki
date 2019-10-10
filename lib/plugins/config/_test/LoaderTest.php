<?php

namespace dokuwiki\plugin\config\test;

use dokuwiki\plugin\config\core\ConfigParser;
use dokuwiki\plugin\config\core\Loader;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class LoaderTest extends \DokuWikiTest {

    protected $pluginsEnabled = ['testing'];

    /**
     * Ensure loading the config meta data works
     */
    public function testMetaData() {
        $loader = new Loader(new ConfigParser());

        $meta = $loader->loadMeta();
        $this->assertTrue(is_array($meta));

        // there should be some defaults
        $this->assertArrayHasKey('savedir', $meta);
        $this->assertEquals(['savedir', '_caution' => 'danger'], $meta['savedir']);
        $this->assertArrayHasKey('proxy____port', $meta);
        $this->assertEquals(['numericopt'], $meta['proxy____port']);

        // there should be plugin info
        $this->assertArrayHasKey('plugin____testing____plugin_settings_name', $meta);
        $this->assertEquals(['fieldset'], $meta['plugin____testing____plugin_settings_name']);
        $this->assertArrayHasKey('plugin____testing____schnibble', $meta);
        $this->assertEquals(['onoff'], $meta['plugin____testing____schnibble']);
    }

    /**
     * Ensure loading the defaults work
     */
    public function testDefaults() {
        $loader = new Loader(new ConfigParser());

        $conf = $loader->loadDefaults();
        $this->assertTrue(is_array($conf));

        // basic defaults
        $this->assertArrayHasKey('title', $conf);
        $this->assertEquals('DokuWiki', $conf['title']);

        // plugin defaults
        $this->assertArrayHasKey('plugin____testing____schnibble', $conf);
        $this->assertEquals(0, $conf['plugin____testing____schnibble']);
    }

    /**
     * Ensure language loading works
     */
    public function testLangs() {
        $loader = new Loader(new ConfigParser());

        $lang = $loader->loadLangs();
        $this->assertTrue(is_array($lang));

        // basics are not included in the returned array!
        $this->assertArrayNotHasKey('title', $lang);

        // plugin strings
        $this->assertArrayHasKey('plugin____testing____plugin_settings_name', $lang);
        $this->assertEquals('Testing', $lang['plugin____testing____plugin_settings_name']);
        $this->assertArrayHasKey('plugin____testing____schnibble', $lang);
        $this->assertEquals(
            'Turns on the schnibble before the frobble is used',
            $lang['plugin____testing____schnibble']
        );
    }
}
