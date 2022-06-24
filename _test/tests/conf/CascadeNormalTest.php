<?php

class CascadeNormalTest extends DokuWikiTest
{

    public function setUp(): void
    {
        $this->pluginsEnabled = [
            'testing'
        ];

        parent::setUp();
    }

    public function testDefaults()
    {
        global $conf;

        $this->assertEquals('start', $conf['start'], 'default value');
        $this->assertEquals('', $conf['tagline'], 'default value');

        $this->assertFalse(isset($conf['plugin']['testing']['schnibble']), 'not set before plugin call');

        $testing = plugin_load('action', 'testing');
        $this->assertEquals(0, $testing->getConf('schnibble'), 'default value');
    }

    public function testLocal()
    {
        global $conf;

        $this->assertEquals('My Test Wiki', $conf['title'], 'overriden in local.php (values from Config manager)');

        $testing = plugin_load('action', 'testing');
        $this->assertEquals('Local setting', $testing->getConf('second'), 'overriden in local.php');
    }
}
