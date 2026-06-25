<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Internallink;
use dokuwiki\Parsing\ParserMode\Windowssharelink;

/**
 * Tests for the {@see Windowssharelink} parser mode: `\\server\share` UNC paths,
 * both as bare text and as the target of an internal `[[ ]]` link.
 *
 * @group parser_links
 */
class WindowssharelinkTest extends ParserTestBase
{
    function testBare() {
        $this->P->addMode('windowssharelink', new Windowssharelink());
        $this->P->parse('Foo \\\server\share Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['windowssharelink', ['\\\server\share', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testBareWithHyphen() {
        $this->P->addMode('windowssharelink', new Windowssharelink());
        $this->P->parse('Foo \\\server\share-hyphen Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['windowssharelink', ['\\\server\share-hyphen', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInsideInternalLink() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse('Foo [[\\\server\share|My Documents]] Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['windowssharelink', ['\\\server\share', 'My Documents']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
