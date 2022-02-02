<?php

namespace dokuwiki\plugin\config\test\Setting;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class SettingNumericoptTest extends SettingNumericTest {

    /** @inheritdoc */
    public function dataOut() {
        return array_merge(
            parent::dataOut(),
            [
                ['', "\$conf['test'] = '';\n"],
            ]
        );
    }

}
