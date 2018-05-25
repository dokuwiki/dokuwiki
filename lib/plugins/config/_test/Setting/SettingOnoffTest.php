<?php

namespace dokuwiki\plugin\config\test\Setting;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class SettingOnoffTest extends SettingTest {

    /** @inheritdoc */
    public function dataOut() {
        return [
            [1, "\$conf['test'] = 1;\n"],
            [0, "\$conf['test'] = 0;\n"],

            ['1', "\$conf['test'] = 1;\n"],
            ['0', "\$conf['test'] = 0;\n"],

            ['on', "\$conf['test'] = 1;\n"],
            ['off', "\$conf['test'] = 0;\n"],

            ['true', "\$conf['test'] = 1;\n"],
            ['false', "\$conf['test'] = 0;\n"],

            ['On', "\$conf['test'] = 1;\n"],
            ['Off', "\$conf['test'] = 0;\n"],

            ['True', "\$conf['test'] = 1;\n"],
            ['False', "\$conf['test'] = 0;\n"],

            [true, "\$conf['test'] = 1;\n"],
            [false, "\$conf['test'] = 0;\n"],

            [3, "\$conf['test'] = 1;\n"],
            ['3', "\$conf['test'] = 1;\n"],

            ['', "\$conf['test'] = 0;\n"],
            ['   ', "\$conf['test'] = 0;\n"],
        ];
    }

    /** @inheritdoc */
    public function dataShouldBeSaved() {
        return [
            [0, null, false],
            [1, null, false],
            [0, 0, false],
            [1, 1, false],
            [0, 1, true],
            [1, 0, true],

            ['0', '0', false],
            ['1', '1', false],
            ['0', '1', true],
            ['1', '0', true],

            ['0', 0, false],
            ['1', 1, false],
            ['0', 1, true],
            ['1', 0, true],

            [0, '0', false],
            [1, '1', false],
            [0, '1', true],
            [1, '0', true],
        ];
    }

}
