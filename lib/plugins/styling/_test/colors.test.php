<?php

/**
 * Color handling tests for the styling plugin
 *
 * @group plugin_styling
 * @group plugins
 */
class colors_plugin_styling_test extends DokuWikiTest
{

    /**
     * @return array
     * @see testColorType
     */
    public function provideColorType()
    {
        return [
            ['foobar', 'text'],
            ['white', 'text'],
            ['#fff', 'color'],
            ['#f0f0f0', 'color'],
            ['#f0f0', 'text'],
            ['some #f0f0f0 color', 'text'],
        ];
    }

    /**
     * @param string $input
     * @param string $expect
     * @dataProvider provideColorType
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testColorType($input, $expect)
    {
        $plugin = new admin_plugin_styling();
        $output = $this->callInaccessibleMethod($plugin, 'colorType', [$input]);
        $this->assertEquals($expect, $output);
    }

    /**
     * @return array
     * @see testColorValue
     */
    public function provideColorValue()
    {
        return [
            ['foobar', 'foobar'],
            ['white', 'white'],
            ['#fff', '#ffffff'],
            ['#123', '#112233'],
            ['#f0f0f0', '#f0f0f0'],
            ['#f0f0', '#f0f0'],
            ['some #f0f0f0 color', 'some #f0f0f0 color'],
        ];
    }

    /**
     * @param string $input
     * @param string $expect
     * @dataProvider provideColorValue
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testColorValue($input, $expect)
    {
        $plugin = new admin_plugin_styling();
        $output = $this->callInaccessibleMethod($plugin, 'colorValue', [$input]);
        $this->assertEquals($expect, $output);
    }
}
