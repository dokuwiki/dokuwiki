<?php

namespace dokuwiki\plugin\config\test\Setting;

use dokuwiki\plugin\config\core\Setting\SettingMulticheckbox;

/**
 * @group plugin_config
 * @group admin_plugins
 * @group plugins
 * @group bundled_plugins
 */
class SettingMulticheckboxTest extends SettingTest {

    /**
     * DataProvider for testUpdate()
     *
     * @return array
     */
    public function dataUpdate() {
        return [
            'checkboxes only' => [['foo', 'bar'], 'foo,bar'],
            'with other' => [['foo', 'other' => ' baz , bang '], 'foo,baz,bang'],
            'empty other' => [['foo', 'other' => ''], 'foo'],
            'other with empty items' => [['foo', 'other' => ',baz,, bang ,'], 'foo,baz,bang'],
            'other with inner space' => [['other' => 'baz bang'], 'baz bang'],
            'nothing' => [[], ''],
        ];
    }

    /**
     * Check that the submitted checkboxes and the other field are combined, also when no other field is sent
     *
     * @dataProvider dataUpdate
     * @param array $input The submitted form values
     * @param string $expect The expected local value
     */
    public function testUpdate($input, $expect) {
        /** @var SettingMulticheckbox $setting */
        $setting = new $this->class('test', ['_choices' => ['foo', 'bar'], '_other' => 'never']);
        $setting->initialize('default');

        $this->assertTrue($setting->update($input));
        $this->assertSame("\$conf['test'] = '$expect';\n", $setting->out('conf'));
    }
}
