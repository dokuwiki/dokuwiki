<?php

namespace dokuwiki\plugin\config\test\Setting;

use dokuwiki\plugin\config\core\Setting\Setting;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class SettingTest extends AbstractSettingTest {

    /**
     * Dataprovider for testOut()
     *
     * @return array
     */
    public function dataOut() {
        return [
            ['bar', "\$conf['test'] = 'bar';\n"],
            ["foo'bar", "\$conf['test'] = 'foo\\'bar';\n"],
        ];
    }

    /**
     * Check the output
     *
     * @param mixed $in The value to initialize the setting with
     * @param string $out The expected output (for conf[test])
     * @dataProvider dataOut
     */
    public function testOut($in, $out) {
        /** @var Setting $setting */
        $setting = new $this->class('test');
        $setting->initialize('ignore', $in);

        $this->assertEquals($out, $setting->out('conf'));
    }

    /**
     * DataProvider for testShouldBeSaved()
     *
     * @return array
     */
    public function dataShouldBeSaved() {
        return [
            ['default', null, false],
            ['default', 'default', false],
            ['default', 'new', true],
        ];
    }

    /**
     * Check if shouldBeSaved works as expected
     *
     * @dataProvider dataShouldBeSaved
     * @param mixed $default The default value
     * @param mixed $local The current local value
     * @param bool $expect The expected outcome
     */
    public function testShouldBeSaved($default, $local, $expect) {
        /** @var Setting $setting */
        $setting = new $this->class('test');
        $setting->initialize($default, $local, null);
        $this->assertSame($expect, $setting->shouldBeSaved());
    }

}
