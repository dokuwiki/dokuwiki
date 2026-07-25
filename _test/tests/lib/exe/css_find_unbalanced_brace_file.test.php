<?php

require_once DOKU_INC.'lib/exe/css.php';

class css_find_unbalanced_brace_file_test extends DokuWikiTest {

    private function buildCss(array $mediatypeFiles) {
        $css = '';
        foreach ($mediatypeFiles as $mediatype => $files) {
            $css .= "\n@media $mediatype {";
            $css .= "/* START $mediatype styles */\n";
            foreach ($files as $path => $content) {
                $css .= "\n/* XXXXXXXXX $path XXXXXXXXX */\n";
                $css .= $content . "\n";
            }
            $css .= "\n} /* /@media END $mediatype styles */\n";
        }
        return $css;
    }

    function test_balanced() {
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => 'body { font-family: sans-serif; }',
                '/lib/plugins/usermanager/screen.less' => '.um { color: black; }',
            ],
            'speech' => [
                '/lib/plugins/usermanager/speech.less' => '.um { color: black; }',
            ],
        ]);
        $this->assertNull(css_find_unbalanced_brace_file($css));
    }

    function test_unclosed_in_early_file() {
        // the scenario from #4707: typo in conf/userstyle.css should be
        // detected, not the last file (usermanager/speech.less)
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => 'body { font-family: sans-serif; }',
                '/lib/plugins/usermanager/screen.less' => '.um { color: black; }',
                '/conf/userstyle.css' => '.dokuwiki {',
                '/lib/plugins/config/screen.less' => '.cfg { color: red; }',
            ],
            'all' => [
                '/lib/styles/all.css' => 'a { color: blue; }',
            ],
            'speech' => [
                '/lib/plugins/usermanager/speech.less' => '.um { color: black; }',
            ],
        ]);
        $this->assertEquals('/conf/userstyle.css', css_find_unbalanced_brace_file($css));
    }

    function test_unclosed_in_last_file() {
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => 'body { font-family: sans-serif; }',
                '/conf/userstyle.css' => '.dokuwiki {',
            ],
        ]);
        $this->assertEquals('/conf/userstyle.css', css_find_unbalanced_brace_file($css));
    }

    function test_extra_closing_brace() {
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => 'body { color: red; } }',
                '/lib/plugins/usermanager/screen.less' => '.um { color: black; }',
            ],
        ]);
        $this->assertEquals('/lib/styles/screen.css', css_find_unbalanced_brace_file($css));
    }

    function test_braces_in_strings() {
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => 'body { content: "{ this is a { string } }"; }',
                '/conf/userstyle.css' => '.dokuwiki { color: red; }',
            ],
        ]);
        $this->assertNull(css_find_unbalanced_brace_file($css));
    }

    function test_braces_in_comments() {
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => "/* a comment with { braces } */\nbody { color: red; }",
                '/conf/userstyle.css' => '.dokuwiki { color: red; }',
            ],
        ]);
        $this->assertNull(css_find_unbalanced_brace_file($css));
    }

    function test_nested_blocks() {
        $css = $this->buildCss([
            'screen' => [
                '/lib/styles/screen.css' => '.outer { .inner { color: red; } }',
                '/conf/userstyle.css' => '.dokuwiki { .nested { color: blue; }',
            ],
            'speech' => [
                '/lib/plugins/usermanager/speech.less' => '.um { color: black; }',
            ],
        ]);
        $this->assertEquals('/conf/userstyle.css', css_find_unbalanced_brace_file($css));
    }

    function test_no_file_headers() {
        $this->assertNull(css_find_unbalanced_brace_file('body { color: red; }'));
    }
}
