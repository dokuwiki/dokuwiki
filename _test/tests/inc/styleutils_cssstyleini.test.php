<?php

class styleutils_cssstyleini_test extends EasyWikiTest {

    function test_mergedstyleini() {
        $util = new \easywiki\StyleUtils('easywiki', false, true);

        $expected = array (
            'stylesheets' =>
                array (
                    'screen' =>
                        array (
                            WIKI_CONF . 'tpl/easywiki/css/_tests.less' => '/',
                            WIKI_INC . 'lib/tpl/easywiki/css/content.less' => '/lib/tpl/easywiki/',
                        ),
                ),
            'replacements' =>
                array (
                    '__text__' => '#333',
                    '__background__' => '#f2ecec',
                    '__custom_variable__' => '#5e4040',
                    '__custom_variable_two__' => 'url(' . WIKI_BASE . 'test/foo.png)',
                ),
        );

        $actual = $util->cssStyleini();

        // check that all stylesheet levels are present
        $this->assertArrayHasKey('all', $actual['stylesheets']);
        $this->assertArrayHasKey('print', $actual['stylesheets']);

        // check an original stylesheet and an additional one
        $this->assertEmpty(
            array_diff_assoc($expected['stylesheets']['screen'], $actual['stylesheets']['screen'])
        );

        // merged config has an original value (text), an overridden value (background) and a new custom replacement (custom_variable)
        $this->assertEmpty(
            array_diff_assoc($expected['replacements'], $actual['replacements'])
        );
    }
}
