<?php

class CascadeProtectedTest extends DokuWikiTest
{

    public function setUp(): void
    {
        global $config_cascade;

        $this->pluginsEnabled = [
            'testing'
        ];

        $out = "<?php\n/*\n * protected settings, cannot modified in the Config manager\n" .
            " * Some test data */\n";
        $out .= "\$conf['title'] = 'Protected Title';\n";
        $out .= "\$conf['tagline'] = 'Protected Tagline';\n";
        $out .= "\$conf['plugin']['testing']['schnibble'] = 1;\n";
        $out .= "\$conf['plugin']['testing']['second'] = 'Protected setting';\n";

        file_put_contents(end($config_cascade['main']['protected']), $out);

        parent::setUp();
    }

    public function testDefaults()
    {
        global $conf;

        $this->assertEquals('Protected Title', $conf['title'], 'protected local value, overrides local');
        $this->assertEquals('Protected Tagline', $conf['tagline'], 'protected local value, override default');

        $testing = plugin_load('action', 'testing');
        $this->assertEquals(1, $testing->getConf('schnibble'), 'protected local value, ');
        $this->assertEquals('Protected setting', $testing->getConf('second'), 'protected local value');
    }

    public function tearDown(): void
    {
        global $config_cascade;

        unlink(end($config_cascade['main']['protected']));

        parent::tearDown();
    }
}
