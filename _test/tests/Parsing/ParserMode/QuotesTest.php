<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Quotes;

class QuotesTest extends ParserTestBase
{

    function setUp() : void {
        parent::setUp();
        global $conf;
        $conf['typography'] = 2;
    }

    function testSingleQuoteOpening() {
        $raw = "Foo 'hello Bar";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['singlequoteopening',[]],
            ['cdata',['hello Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testSingleQuoteOpeningSpecial() {
        $raw = "Foo said:'hello Bar";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo said:']],
            ['singlequoteopening',[]],
            ['cdata',['hello Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testSingleQuoteClosing() {
        $raw = "Foo hello' Bar";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo hello']],
            ['singlequoteclosing',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testSingleQuoteClosingSpecial() {
        $raw = "Foo hello') Bar";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo hello']],
            ['singlequoteclosing',[]],
            ['cdata',[') Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testSingleQuotes() {
        $raw = "Foo 'hello' Bar";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['singlequoteopening',[]],
            ['cdata',['hello']],
            ['singlequoteclosing',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testApostrophe() {
        $raw = "hey it's fine weather today";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'hey it']],
            ['apostrophe',[]],
            ['cdata',['s fine weather today']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }


    function testSingleQuotesSpecial() {
        $raw = "Foo ('hello') Bar";
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo (']],
            ['singlequoteopening',[]],
            ['cdata',['hello']],
            ['singlequoteclosing',[]],
            ['cdata',[') Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuoteOpening() {
        $raw = 'Foo "hello Bar';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['doublequoteopening',[]],
            ['cdata',['hello Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuoteOpeningSpecial() {
        $raw = 'Foo said:"hello Bar';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo said:']],
            ['doublequoteopening',[]],
            ['cdata',['hello Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuoteClosing() {
        $raw = 'Foo hello" Bar';
        $this->P->addMode('quotes', new Quotes());
        $this->H->setStatus('doublequote', 1);
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo hello']],
            ['doublequoteclosing',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuoteClosingSpecial() {
        $raw = 'Foo hello") Bar';
        $this->P->addMode('quotes', new Quotes());
        $this->H->setStatus('doublequote', 1);

        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo hello']],
            ['doublequoteclosing',[]],
            ['cdata',[') Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }
    function testDoubleQuoteClosingSpecial2() {
        $raw = 'Foo hello") Bar';
        $this->P->addMode('quotes', new Quotes());

        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo hello']],
            ['doublequoteopening',[]],
            ['cdata',[') Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuotes() {
        $raw = 'Foo "hello" Bar';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['doublequoteopening',[]],
            ['cdata',['hello']],
            ['doublequoteclosing',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuotesSpecial() {
        $raw = 'Foo ("hello") Bar';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo (']],
            ['doublequoteopening',[]],
            ['cdata',['hello']],
            ['doublequoteclosing',[]],
            ['cdata',[') Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

    function testDoubleQuotesEnclosingBrackets() {
        $raw = 'Foo "{hello}" Bar';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['doublequoteopening',[]],
            ['cdata',['{hello}']],
            ['doublequoteclosing',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext - '.$raw);
    }

    function testDoubleQuotesEnclosingLink() {
        $raw = 'Foo "[[www.domain.com]]" Bar';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['doublequoteopening',[]],
            ['cdata',['[[www.domain.com]]']],
            ['doublequoteclosing',[]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }


    function testAllQuotes() {
        $raw = 'There was written "He thought \'It\'s a man\'s world\'".';
        $this->P->addMode('quotes',new Quotes());
        $this->P->parse($raw);

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'There was written ']],
            ['doublequoteopening',[]],
            ['cdata',['He thought ']],
            ['singlequoteopening',[]],
            ['cdata',['It']],
            ['apostrophe',[]],
            ['cdata',['s a man']],
            ['apostrophe',[]],
            ['cdata',['s world']],
            ['singlequoteclosing',[]],
            ['doublequoteclosing',[]],
            ['cdata',["."]],
            ['p_close',[]],
            ['document_end',[]],
        ];

        $this->assertCalls($calls, $this->H->calls, 'wikitext => '.$raw);
    }

}
