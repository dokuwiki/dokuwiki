<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Manager;
use DokuWikiTest;
use org\bovigo\vfs\vfsStream;

/**
 * Tests for the Manager class
 *
 * @group plugin_extension
 * @group plugins
 */
class ManagerTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['extension'];

    /**
     * Tests a full cycle of manager.dat operations
     *
     */
    public function testCycle()
    {
        $root = io_mktmpdir();

        $extension = $this->createMock(Extension::class);
        $extension->method('getInstallDir')->willReturn($root);

        $manager = new Manager($extension);

        $this->assertNull($manager->getLastUpdate());
        $this->assertEmpty($manager->getDownloadURL());

        $manager->storeUpdate('http://example.com/firstinstall');

        $this->assertFileExists($root . '/manager.dat');
        $content = file_get_contents($root . '/manager.dat');
        $this->assertStringContainsString('downloadurl=http://example.com/firstinstall', $content);
        $this->assertStringContainsString('installed=', $content);

        $updated = $manager->getLastUpdate();
        $installed = $manager->getInstallDate();

        $this->assertInstanceOf(\DateTime::class, $updated);
        $this->assertInstanceOf(\DateTime::class, $installed);
        $this->assertEquals($updated, $installed);
        $this->assertEquals('http://example.com/firstinstall', $manager->getDownloadURL());

        $this->waitForTick();
        $manager->storeUpdate('http://example.com/update');

        $updated = $manager->getLastUpdate();
        $installed = $manager->getInstallDate();

        $this->assertInstanceOf(\DateTime::class, $updated);
        $this->assertInstanceOf(\DateTime::class, $installed);
        $this->assertNotEquals($updated, $installed);
        $this->assertEquals('http://example.com/update', $manager->getDownloadURL());
    }
}
