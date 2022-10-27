<?php

namespace dokuwiki\plugin\config\test;
use dokuwiki\plugin\config\core\Setting\SettingString;
use dokuwiki\plugin\config\core\Writer;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class WriterTest extends \DokuWikiTest {

    public function testSave() {
        global $config_cascade;
        $config = end($config_cascade['main']['local']);

        $set1 = new SettingString('test1');
        $set1->initialize('foo','bar', null);
        $set2 = new SettingString('test2');
        $set2->initialize('foo','foo', null);
        $settings = [$set1, $set2];
        $writer = new Writer();

        // before running, no backup should exist
        $this->assertFileExists($config);
        $this->assertFileNotExists("$config.bak.php");
        $old = filesize($config);

        /** @noinspection PhpUnhandledExceptionInspection */
        $writer->save($settings);

        // after running, both should exist
        $this->assertFileExists($config);
        $this->assertFileExists("$config.bak.php");
        $this->assertEquals($old, filesize("$config.bak.php"), 'backup should have size of old file');

        // check contents
        $conf = [];
        include $config;
        $this->assertArrayHasKey('test1', $conf);
        $this->assertEquals('bar', $conf['test1']);
        $this->assertArrayNotHasKey('test2', $conf);

        /** @noinspection PhpUnhandledExceptionInspection */
        $writer->save($settings);
        $this->assertEquals(filesize($config), filesize("$config.bak.php"));
    }

    public function testTouch() {
        global $config_cascade;
        $config = end($config_cascade['main']['local']);
        $writer = new Writer();

        $old = filemtime($config);
        $this->waitForTick(true);
        /** @noinspection PhpUnhandledExceptionInspection */
        $writer->touch();
        clearstatcache($config);
        $this->assertGreaterThan($old, filemtime($config));
    }

    public function testEmpty() {
        $writer = new Writer();
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('empty config');
        $writer->save([]);
    }
}
