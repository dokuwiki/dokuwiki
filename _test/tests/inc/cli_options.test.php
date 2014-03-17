<?php

class cli_options extends DokuWikiTest {

    function test_simpleshort() {
        $options = new DokuCLI_Options();
        $options->registerOption('exclude', 'exclude files', 'x', true);

        $args = array('-x', 'foo', 'bang');
        $options->parseOptions($args);

        $this->assertEquals('foo', $options->getOpt('exclude'));
        $this->assertEquals(array('bang'), $args);
        $this->assertFalse($options->getOpt('nothing'));
    }

    function test_simplelong1() {
        $options = new DokuCLI_Options();
        $options->registerOption('exclude', 'exclude files', 'x', true);

        $args = array('--exclude', 'foo', 'bang');
        $options->parseOptions($args);

        $this->assertEquals('foo', $options->getOpt('exclude'));
        $this->assertEquals(array('bang'), $args);
        $this->assertFalse($options->getOpt('nothing'));
    }

    function test_simplelong2() {
        $options = new DokuCLI_Options();
        $options->registerOption('exclude', 'exclude files', 'x', true);

        $args = array('--exclude=foo', 'bang');
        $options->parseOptions($args);

        $this->assertEquals('foo', $options->getOpt('exclude'));
        $this->assertEquals(array('bang'), $args);
        $this->assertFalse($options->getOpt('nothing'));
    }

    function test_complex() {
        $options = new DokuCLI_Options();

        $options->registerOption('plugins', 'run on plugins only', 'p');
        $options->registerCommand('status', 'display status info');
        $options->registerOption('long', 'display long lines', 'l', false, 'status');

        $args = array('-p', 'status', '--long', 'foo');
        $options->parseOptions($args);

        $this->assertEquals('status', $options->getCmd());
        $this->assertTrue($options->getOpt('plugins'));
        $this->assertTrue($options->getOpt('long'));
        $this->assertEquals(array('foo'), $args);
    }
}