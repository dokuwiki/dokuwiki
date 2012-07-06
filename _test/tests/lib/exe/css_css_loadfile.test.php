<?php

require_once DOKU_INC.'lib/exe/css.php';

class css_css_loadfile_test extends DokuWikiTest {
    public function setUp() {
        $this->file = tempnam('/tmp', 'css');
    }

    private function csstest($input, $output = null, $location = 'http://www.example.com/') {
        io_saveFile($this->file, $input);
        $this->assertEquals(css_loadfile($this->file, $location), (is_null($output) ? $input : $output));
    }

    public function test_url_relative() {
        $this->csstest('#test { background: url("test/test.png"); }', '#test { background: url("http://www.example.com/test/test.png"); }');
        $this->csstest('#test { background: url(\'test/test.png\'); }', '#test { background: url(\'http://www.example.com/test/test.png\'); }');
    }

    public function test_url_absolute() {
        $this->csstest('#test { background: url("/test/test.png"); }');
        $this->csstest('#test { background: url(\'/test/test.png\'); }');
    }

    public function test_url_with_protocol() {
        $this->csstest('#test { background: url("http://www.test.com/test/test.png"); }');
        $this->csstest('#test { background: url("https://www.test.com/test/test.png"); }');
        $this->csstest('#test { background: url(\'http://www.test.com/test/test.png\'); }');
        $this->csstest('#test { background: url(\'https://www.test.com/test/test.png\'); }');
    }

    public function test_import_relative() {
        $this->csstest('@import "test/test.png";', '@import "http://www.example.com/test/test.png";');
        $this->csstest('@import \'test/test.png\';', '@import \'http://www.example.com/test/test.png\';');
    }

    public function test_import_absolute() {
        $this->csstest('@import "/test/test.png";');
        $this->csstest('@import \'/test/test.png\';');
    }

    public function test_import_with_protocol() {
        $this->csstest('@import "http://www.test.com/test/test.png";');
        $this->csstest('@import "https://www.test.com/test/test.png";');
        $this->csstest('@import \'http://www.test.com/test/test.png\';');
        $this->csstest('@import \'https://www.test.com/test/test.png\';');
    }

    public function tearDown() {
        unlink($this->file);
        unset($this->file);
    }
}

//Setup VIM: ex: et ts=4 sw=4 :
