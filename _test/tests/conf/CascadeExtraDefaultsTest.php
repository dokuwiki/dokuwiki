<?php

class CascadeExtraDefaultsTest extends DokuWikiTest
{

    protected $oldSetting = [];

    public function setUp(): void
    {
        global $config_cascade;

        $this->pluginsEnabled = [
            'testing'
        ];

        $out = "<?php\n/*\n * protected settings, cannot modified in the Config manager\n" .
            " * Some test data */\n";
        $out .= "\$conf['title'] = 'New default Title';\n";
        $out .= "\$conf['tagline'] = 'New default Tagline';\n";
        $out .= "\$conf['plugin']['testing']['schnibble'] = 1;\n";
        $out .= "\$conf['plugin']['testing']['second'] = 'New default setting';\n";

        $file = DOKU_CONF . 'otherdefaults.php';
        file_put_contents($file, $out);

        //store original settings
        $this->oldSetting = $config_cascade['main']['default'];
        //add second file with defaults, which override the defaults of DokuWiki
        $config_cascade['main']['default'][] = $file;

        parent::setUp();
    }

    public function testNewDefaults()
    {
        global $conf;

        $this->assertEquals('New default Tagline', $conf['tagline'], 'new default value');
        $testing = plugin_load('action', 'testing');
        $this->assertEquals(1, $testing->getConf('schnibble'), 'new default value');


        $this->assertEquals('My Test Wiki', $conf['title'], 'local value still overrides default');
        $this->assertEquals('Local setting', $testing->getConf('second'), 'local value still overrides default');
    }

    public function tearDown(): void
    {
        global $config_cascade;

        $config_cascade['main']['default'] = $this->oldSetting;
        unlink(DOKU_CONF . 'otherdefaults.php');

        parent::tearDown();
    }
}
