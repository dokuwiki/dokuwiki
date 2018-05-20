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
            // FIXME we probably want to handle other values better
        ];
    }

}
