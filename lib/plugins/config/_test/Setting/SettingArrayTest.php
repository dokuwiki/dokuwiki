<?php

namespace dokuwiki\plugin\config\test\Setting;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class SettingArrayTest extends SettingTest {

    /** @inheritdoc */
    public function dataOut() {
        return [
            [ ['foo','bar'], "\$conf['test'] = array('foo', 'bar');\n"]
        ];
    }

}
