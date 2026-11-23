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

    /**
     * Data provider for testCleanURL
     *
     * @return array[] URL and expected result
     */
    public function provideCleanURL()
    {
        return [
            ['https://example.com/plugin.zip', 'https://example.com/plugin.zip'],
            ['https://example.com/plugin.zip?token=secret', 'https://example.com/plugin.zip'],
            ['https://example.com/plugin.zip#frag', 'https://example.com/plugin.zip'],
            ['https://user:pass@example.com/plugin.zip', 'https://example.com/plugin.zip'],
            ['https://user@example.com/plugin.zip?a=b#c', 'https://example.com/plugin.zip'],
            ['https://example.com/@user/plugin.zip', 'https://example.com/@user/plugin.zip'],
            ['https://example.com:8080/plugin.zip?a=b', 'https://example.com:8080/plugin.zip'],
        ];
    }

    /**
     * Credentials, query and fragment are removed from URLs
     *
     * @dataProvider provideCleanURL
     * @param string $url
     * @param string $expected
     */
    public function testCleanURL($url, $expected)
    {
        $extension = $this->createMock(Extension::class);
        $extension->method('getInstallDir')->willReturn(io_mktmpdir());
        $manager = new Manager($extension);

        $this->assertEquals($expected, $this->callInaccessibleMethod($manager, 'cleanURL', [$url]));
    }

    /**
     * Secrets in the download URL are not written to manager.dat
     */
    public function testStoreUpdateRemovesSecrets()
    {
        $root = io_mktmpdir();

        $extension = $this->createMock(Extension::class);
        $extension->method('getInstallDir')->willReturn($root);

        $manager = new Manager($extension);
        $manager->storeUpdate('https://user:pass@example.com/plugin.zip?token=secret');

        $content = file_get_contents($root . '/manager.dat');
        $this->assertStringContainsString('downloadurl=https://example.com/plugin.zip' . DOKU_LF, $content);
        $this->assertStringNotContainsString('secret', $content);
        $this->assertStringNotContainsString('pass', $content);
    }

    /**
     * Secrets in an existing manager.dat are not returned, and do not cause a URL change
     */
    public function testExistingSecrets()
    {
        $root = io_mktmpdir();
        file_put_contents($root . '/manager.dat', "downloadurl=https://user:pass@example.com/plugin.zip?token=secret\n");

        $extension = $this->createMock(Extension::class);
        $extension->method('getInstallDir')->willReturn($root);
        $manager = new Manager($extension);

        $this->assertEquals('https://example.com/plugin.zip', $manager->getDownloadURL());
        $this->assertFalse($manager->hasChangedURL('https://example.com/plugin.zip?token=other'));
        $this->assertTrue($manager->hasChangedURL('https://example.com/other.zip'));
        $this->assertFalse($manager->hasChangedURL(''));
    }
}
