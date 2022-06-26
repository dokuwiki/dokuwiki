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
class LoaderExtraDefaultsTest extends \DokuWikiTest
{

    protected $pluginsEnabled = ['testing'];

    protected $oldSetting = [];

    public function setUp(): void
    {
        global $config_cascade;

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

    /**
     * Ensure loading the defaults work, and that the extra default for plugins provided via an extra main default file
     * override the plugin defaults as well
     */
    public function testDefaultsOverwriting()
    {
        $loader = new Loader(new ConfigParser());

        $conf = $loader->loadDefaults();
        $this->assertTrue(is_array($conf));

        // basic defaults
        $this->assertArrayHasKey('title', $conf);
        $this->assertEquals('New default Title', $conf['title']);
        $this->assertEquals('New default Tagline', $conf['tagline']);

        // plugin defaults
        $this->assertArrayHasKey('plugin____testing____schnibble', $conf);
        $this->assertEquals(1, $conf['plugin____testing____schnibble']);
        $this->assertEquals('New default setting', $conf['plugin____testing____second']);

    }

    public function tearDown(): void
    {
        global $config_cascade;

        $config_cascade['main']['default'] = $this->oldSetting;
        unlink(DOKU_CONF . 'otherdefaults.php');

        parent::tearDown();
    }

}
