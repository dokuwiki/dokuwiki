<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Deleted;
use dokuwiki\Parsing\ParserMode\Emphasis;
use dokuwiki\Parsing\ParserMode\Monospace;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Subscript;
use dokuwiki\Parsing\ParserMode\Superscript;
use dokuwiki\Parsing\ParserMode\Underline;

/**
 * Tests for the individual formatting modes (bold, italic, underline, etc.)
 */
class FormattingTest extends ParserTestBase
{
    function testStrong()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse('Foo **Bar** Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['Bar']],
            ['strong_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmphasis()
    {
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse('Foo //Bar// Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['emphasis_open', []],
            ['cdata', ['Bar']],
            ['emphasis_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnderline()
    {
        $this->P->addMode('underline', new Underline());
        $this->P->parse('Foo __Bar__ Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['underline_open', []],
            ['cdata', ['Bar']],
            ['underline_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMonospace()
    {
        $this->P->addMode('monospace', new Monospace());
        $this->P->parse("Foo ''Bar'' Baz");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['monospace_open', []],
            ['cdata', ['Bar']],
            ['monospace_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSubscript()
    {
        $this->P->addMode('subscript', new Subscript());
        $this->P->parse('Foo <sub>Bar</sub> Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['subscript_open', []],
            ['cdata', ['Bar']],
            ['subscript_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSuperscript()
    {
        $this->P->addMode('superscript', new Superscript());
        $this->P->parse('Foo <sup>Bar</sup> Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['superscript_open', []],
            ['cdata', ['Bar']],
            ['superscript_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testDeleted()
    {
        $this->P->addMode('deleted', new Deleted());
        $this->P->parse('Foo <del>Bar</del> Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['deleted_open', []],
            ['cdata', ['Bar']],
            ['deleted_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNesting()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->addMode('emphasis', new Emphasis());
        $this->P->parse('Foo **bold //and italic// text** Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['bold ']],
            ['emphasis_open', []],
            ['cdata', ['and italic']],
            ['emphasis_close', []],
            ['cdata', [' text']],
            ['strong_close', []],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNoSelfNesting()
    {
        $this->P->addMode('strong', new Strong());
        $this->P->parse('Foo **bold **not nested** end** Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['bold ']],
            ['strong_close', []],
            ['cdata', ['not nested']],
            ['strong_open', []],
            ['cdata', [' end']],
            ['strong_close', []],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
