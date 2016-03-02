<?php

namespace plugin\struct\test;

use plugin\struct\types\Media;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the Media Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Media_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateFailProvider() {
        return array(
            array('image/jpeg, image/png', 'foo.gif'),
            array('image/jpeg, image/png', 'http://www.example.com/foo.gif'),
            array('application/octet-stream', 'hey:joe.jpeg'),
            array('application/octet-stream', 'http://www.example.com/hey:joe.jpeg'),
        );
    }

    /**
     * Provides successful validation data
     *
     * @return array
     */
    public function validateSuccessProvider() {
        return array(
            array('image/jpeg, image/png', 'foo.png'),
            array('image/jpeg, image/png', 'http://www.example.com/foo.png'),
            array('image/jpeg, image/png', 'http://www.example.com/dynamic?.png'),
            array('application/octet-stream', 'hey:joe.exe'),
            array('application/octet-stream', 'http://www.example.com/hey:joe.exe'),

        );
    }

    /**
     * @expectedException \plugin\struct\meta\ValidationException
     * @dataProvider validateFailProvider
     */
    public function test_validate_fail($mime, $value) {
        $integer = new Media(array('mime' => $mime));
        $integer->validate($value);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function test_validate_success($mime, $value) {
        $integer = new Media(array('mime' => $mime));
        $integer->validate($value);
        $this->assertTrue(true); // we simply check that no exceptions are thrown
    }
}
