<?php

class styleutils_cssstyleini_test extends DokuWikiTest {

    function test_mergedstyleini() {
        $util = new \dokuwiki\StyleUtils('dokuwiki', false, true);

        $expected = array (
            'stylesheets' =>
                array (
                    'screen' =>
                        array (
                            DOKU_INC . 'lib/tpl/dokuwiki/css/basic.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_imgdetail.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_media_popup.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_media_fullscreen.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_fileuploader.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_tabs.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_links.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_toc.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_footnotes.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_search.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_recent.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_diff.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_edit.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_modal.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_forms.css' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/_admin.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/structure.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/design.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/usertools.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/pagetools.less' => '/./lib/tpl/dokuwiki/',
                            DOKU_INC . 'lib/tpl/dokuwiki/css/content.less' => '/./lib/tpl/dokuwiki/',
                        ),
                    'all' =>
                        array (
                            DOKU_INC . 'lib/tpl/dokuwiki/css/mobile.less' => '/./lib/tpl/dokuwiki/',
                        ),
                    'print' =>
                        array (
                            DOKU_INC . 'lib/tpl/dokuwiki/css/print.css' => '/./lib/tpl/dokuwiki/',
                        ),
                ),
            'replacements' =>
                array (
                    '__text__' => '#5e4040',
                    '__background__' => '#f2ecec',
                    '__text_alt__' => '#b39292',
                    '__background_alt__' => '#dbcbcb',
                    '__text_neu__' => '#664747',
                    '__background_neu__' => '#b09f9f',
                    '__border__' => '#d8b0b0',
                    '__highlight__' => '#d699ff',
                    '__link__' => '#44703c',
                    '__background_site__' => '#c4d8d6',
                    '__existing__' => '#154b15',
                    '__missing__' => '#c900db',
                    '__site_width__' => '85em',
                    '__sidebar_width__' => '14em',
                    '__tablet_width__' => '820px',
                    '__phone_width__' => '490px',
                    '__theme_color__' => '#004489',
                ),
        );

        $actual = $util->cssStyleini();

        $this->assertEquals($expected, $actual);
    }
}
