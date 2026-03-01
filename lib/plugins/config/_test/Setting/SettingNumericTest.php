<?php

namespace dokuwiki\plugin\config\test\Setting;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class SettingNumericTest extends SettingTest {

    /** @inheritdoc */
    public function dataOut() {
        return [
            [42, "\$conf['test'] = 42;\n"],
            [0, "\$conf['test'] = 0;\n"],
            [-42, "\$conf['test'] = -42;\n"],
            [-42.13, "\$conf['test'] = -42.13;\n"],
            ['12*13', "\$conf['test'] = 12*13;\n"],
        ];
    }

}
