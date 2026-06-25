<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Camelcaselink;

/**
 * Tests for the {@see Camelcaselink} parser mode: bare CamelCase identifiers become internal page links.
 *
 * @group parser_links
 */
class CamelcaselinkTest extends ParserTestBase
{
    function testCamelCase() {
        $this->P->addMode('camelcaselink', new Camelcaselink());
        $this->P->parse("Foo FooBar Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['camelcaselink', ['FooBar']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
