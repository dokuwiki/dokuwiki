<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\File;

class FileTest extends ParserTestBase
{

    function setUp() : void {
        parent::setUp();
        $this->P->addMode('file',new File());
    }

    function testFile() {
        $this->P->parse('Foo <file>Test</file> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['file',['Test',null,null]],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFileHighlightDownload() {
        $this->P->parse('Foo <file txt test.txt>Test</file> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['file',['Test','txt','test.txt']],
            ['p_open',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFileToken() {
        $this->P->parse('Foo <file2>Test</file2> Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo <file2>Test</file2> Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

}
