<?php

namespace dokuwiki\plugin\indexmenu\test;

use DokuWikiTest;

/**
 * General tests for the indexmenu plugin
 *
 * @group plugin_indexmenu
 * @group plugins
 */
class GeneralTest extends DokuWikiTest
{

    /**
     * Simple test to make sure the plugin.info.txt is in correct format
     */
    public function testPluginInfo(): void
    {
        $file = __DIR__ . '/../plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertArrayHasKey('author', $info);
        $this->assertArrayHasKey('email', $info);
        $this->assertArrayHasKey('date', $info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('desc', $info);
        $this->assertArrayHasKey('url', $info);

        $this->assertEquals('indexmenu', $info['base']);
        $this->assertRegExp('/^https?:\/\//', $info['url']);
        $this->assertTrue(mail_isvalid($info['email']));
        $this->assertRegExp('/^\d\d\d\d-\d\d-\d\d$/', $info['date']);
        $this->assertTrue(false !== strtotime($info['date']));
    }

    /**
     * Test to ensure that every conf['...'] entry in conf/default.php has a corresponding meta['...'] entry in
     * conf/metadata.php.
     */
    public function testPluginConf(): void
    {
        $conf_file = __DIR__ . '/../conf/default.php';
        $meta_file = __DIR__ . '/../conf/metadata.php';

        if (!file_exists($conf_file) && !file_exists($meta_file)) {
            self::markTestSkipped('No config files exist -> skipping test');
        }

        if (file_exists($conf_file)) {
            include($conf_file);
        }
        if (file_exists($meta_file)) {
            include($meta_file);
        }

        $this->assertEquals(
            gettype($conf),
            gettype($meta),
            'Both ' . DOKU_PLUGIN . 'indexmenu/conf/default.php and ' . DOKU_PLUGIN . 'indexmenu/conf/metadata.php have to exist and contain the same keys.'
        );

        if ($conf !== null && $meta !== null) {
            foreach ($conf as $key => $value) {
                $this->assertArrayHasKey(
                    $key,
                    $meta,
                    'Key $meta[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'indexmenu/conf/metadata.php'
                );
            }

            foreach ($meta as $key => $value) {
                $this->assertArrayHasKey(
                    $key,
                    $conf,
                    'Key $conf[\'' . $key . '\'] missing in ' . DOKU_PLUGIN . 'indexmenu/conf/default.php'
                );
            }
        }

    }
}
