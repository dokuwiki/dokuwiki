<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Filelink;

/**
 * Tests for the {@see Filelink} parser mode: bare `file://...` URLs.
 *
 * @group parser_links
 */
class FilelinkTest extends ParserTestBase
{
    function testFileLink() {
        $this->P->addMode('filelink', new FileLink());
        $this->P->parse('Foo file://temp/file.txt Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['filelink', ['file://temp/file.txt ', null]],
            ['cdata', ['Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
