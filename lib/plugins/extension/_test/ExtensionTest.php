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

    public function testSomething()
    {
        $extension = Extension::createFromDirectory(__DIR__.'/../');

        $this->assertFalse($extension->isTemplate());
        $this->assertEquals('extension', $extension->getBase());
    }
}
