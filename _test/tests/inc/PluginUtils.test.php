<?php

class PluginUtilsTest extends DokuWikiTest
{
    /**
     * @covers ::plugin_list()
     */
    public function test_cache_cleaning_cleanToUnclean()
    {
        $expectedListOfPlugins = [
            'acl',
            'authplain',
            'config',
            'info',
            'popularity',
            'revert',
            'safefnrecode',
            'usermanager',
        ];

        $this->assertEquals($expectedListOfPlugins, plugin_list());
    }
}
