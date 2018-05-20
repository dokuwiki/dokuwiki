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
     * Dataprovider for test out
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
}
