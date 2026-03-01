<?php

namespace dokuwiki\plugin\config\test;

use dokuwiki\plugin\config\core\ConfigParser;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class ConfigParserTest extends \DokuWikiTest {

    function test_readconfig() {
        $parser = new ConfigParser();
        $conf = $parser->parse(__DIR__ . '/data/config.php');

        // var_dump($conf);

        $this->assertEquals('42', $conf['int1']);
        $this->assertEquals('6*7', $conf['int2']);

        $this->assertEquals('Hello World', $conf['str1']);
        $this->assertEquals('G\'day World', $conf['str2']);
        $this->assertEquals('Hello World', $conf['str3']);
        $this->assertEquals("Hello 'World'", $conf['str4']);
        $this->assertEquals('Hello "World"', $conf['str5']);

        $this->assertEquals(array('foo', 'bar', 'baz'), $conf['arr1']);
    }

    function test_readconfig_onoff() {
        $parser = new ConfigParser();
        $conf = $parser->parse(__DIR__ . '/data/config.php');

        // var_dump($conf);

        $this->assertEquals(0, $conf['onoff1']);
        $this->assertEquals(1, $conf['onoff2']);
        $this->assertEquals(2, $conf['onoff3']);
        $this->assertEquals(0, $conf['onoff4']);
        $this->assertEquals(1, $conf['onoff5']);
        $this->assertEquals(false, $conf['onoff6']);
        $this->assertEquals(true, $conf['onoff7']);
        $this->assertEquals('false', $conf['onoff8']);
        $this->assertEquals('true', $conf['onoff9']);

        $this->assertEquals('false senctence', $conf['str11']);
        $this->assertEquals('true sentence', $conf['str12']);
        $this->assertEquals('truesfdf', $conf['str13']);
        $this->assertEquals("true", $conf['str14']);
        $this->assertEquals("truesfdsf", $conf['str15']);

        $this->assertTrue($conf['onoff1'] == false);
        $this->assertTrue($conf['onoff2'] == true);
        $this->assertTrue($conf['onoff3'] == true);
        $this->assertTrue($conf['onoff4'] == false);
        $this->assertTrue($conf['onoff5'] == true);
        $this->assertTrue($conf['onoff6'] == false);
        $this->assertTrue($conf['onoff7'] == true);
        $this->assertTrue($conf['onoff8'] == true); //string
        $this->assertTrue($conf['onoff9'] == true); //string
    }

}
