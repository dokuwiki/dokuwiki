<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Emaillink;

/**
 * Tests for the {@see Emaillink} parser mode: bare email addresses inside `<...>` envelopes.
 *
 * @group parser_links
 */
class EmaillinkTest extends ParserTestBase
{
    function testEmail() {
        $this->P->addMode('emaillink', new Emaillink());
        $this->P->parse("Foo <bugs@php.net> Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['emaillink', ['bugs@php.net', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmailRFC2822() {
        $this->P->addMode('emaillink', new Emaillink());
        $this->P->parse("Foo <~fix+bug's.for/ev{e}r@php.net> Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['emaillink', ["~fix+bug's.for/ev{e}r@php.net", null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmailCase() {
        $this->P->addMode('emaillink', new Emaillink());
        $this->P->parse("Foo <bugs@pHp.net> Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['emaillink', ['bugs@pHp.net', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
