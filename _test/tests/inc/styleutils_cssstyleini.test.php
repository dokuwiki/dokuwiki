<?php

class styleutils_cssstyleini_test extends DokuWikiTest {

    function test_mergedstyleini() {
        $util = new \dokuwiki\StyleUtils('dokuwiki', false, true);

        $expected = array (
            'stylesheets' =>
                array (
                    'screen' =>
                        array (
                            DOKU_CONF . 'tpl/dokuwiki/css/_tests.less' => '/./',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/content.less' => '/./lib/tpl/dokuwiki/',
                        ),
                ),
            'replacements' =>
                array (
                    '__text__' => '#333',
                    '__background__' => '#f2ecec',
                    '__custom_variable__' => '#5e4040',
                    '__custom_variable_two__' => 'url(' . DOKU_BASE . 'test/foo.png)',
                ),
        );

        $actual = $util->cssStyleini();

        // check that all stylesheet levels are present
        $this->assertArrayHasKey('all', $actual['stylesheets']);
        $this->assertArrayHasKey('print', $actual['stylesheets']);

        // check an original stylesheet and an additional one
        $this->assertArraySubset($expected['stylesheets']['screen'], $actual['stylesheets']['screen']);

        // merged config has an original value (text), an overridden value (background) and a new custom replacement (custom_variable)
        $this->assertArraySubset($expected['replacements'], $actual['replacements']);
    }
}
