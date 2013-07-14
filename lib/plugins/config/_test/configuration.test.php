<?php

class plugin_config_configuration_test extends DokuWikiTest {

    private $config = '';
    private $meta = '';

    function __construct() {
        $this->config = dirname(__FILE__).'/data/config.php';
        $this->meta   = dirname(__FILE__).'/data/metadata.php';
        require_once(dirname(__FILE__).'/../settings/config.class.php');
    }

    function test_readconfig() {
        $confmgr = new configuration($this->meta);

        $conf = $confmgr->_read_config($this->config);

        //print_r($conf);

        $this->assertEquals('42', $conf['int1']);
        $this->assertEquals('6*7', $conf['int2']);

        $this->assertEquals('Hello World', $conf['str1']);
        $this->assertEquals('G\'day World', $conf['str2']);
        $this->assertEquals('Hello World', $conf['str3']);
        $this->assertEquals("Hello 'World'", $conf['str4']);
        $this->assertEquals('Hello "World"', $conf['str5']);

        $this->assertEquals(array('foo', 'bar', 'baz'), $conf['arr1']);
    }

}
