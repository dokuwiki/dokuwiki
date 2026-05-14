<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Rss;

class RssTest extends ParserTestBase
{
    function setUp(): void
    {
        parent::setUp();
        $this->P->addMode('rss', new Rss());
    }

    function testRssBasic()
    {
        $this->P->parse('Foo {{rss>http://example.com/feed}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['p_close', []],
            ['rss', ['http://example.com/feed', [
                'max' => 8,
                'reverse' => 0,
                'author' => 0,
                'date' => 0,
                'details' => 0,
                'nosort' => 0,
                'refresh' => 14400,
            ]]],
            ['p_open', []],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssMaxItems()
    {
        $this->P->parse('{{rss>http://example.com/feed 5}}');
        $calls = [
            ['document_start', []],
            ['rss', ['http://example.com/feed', [
                'max' => '5',
                'reverse' => 0,
                'author' => 0,
                'date' => 0,
                'details' => 0,
                'nosort' => 0,
                'refresh' => 14400,
            ]]],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssAllParams()
    {
        $this->P->parse('{{rss>http://example.com/feed 3 rev author date desc nosort 2h}}');
        $calls = [
            ['document_start', []],
            ['rss', ['http://example.com/feed', [
                'max' => '3',
                'reverse' => 1,
                'author' => 1,
                'date' => 1,
                'details' => 1,
                'nosort' => 1,
                'refresh' => 7200,
            ]]],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssRefreshDays()
    {
        $this->P->parse('{{rss>http://example.com/feed 1d}}');
        $calls = [
            ['document_start', []],
            ['rss', ['http://example.com/feed', [
                'max' => 8,
                'reverse' => 0,
                'author' => 0,
                'date' => 0,
                'details' => 0,
                'nosort' => 0,
                'refresh' => 86400,
            ]]],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssRefreshMinimum()
    {
        $this->P->parse('{{rss>http://example.com/feed 1m}}');
        $calls = [
            ['document_start', []],
            ['rss', ['http://example.com/feed', [
                'max' => 8,
                'reverse' => 0,
                'author' => 0,
                'date' => 0,
                'details' => 0,
                'nosort' => 0,
                'refresh' => 600,
            ]]],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssDetailAlias()
    {
        $this->P->parse('{{rss>http://example.com/feed detail}}');
        $calls = [
            ['document_start', []],
            ['rss', ['http://example.com/feed', [
                'max' => 8,
                'reverse' => 0,
                'author' => 0,
                'date' => 0,
                'details' => 1,
                'nosort' => 0,
                'refresh' => 14400,
            ]]],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssAuthorAlias()
    {
        $this->P->parse('{{rss>http://example.com/feed by}}');
        $calls = [
            ['document_start', []],
            ['rss', ['http://example.com/feed', [
                'max' => 8,
                'reverse' => 0,
                'author' => 1,
                'date' => 0,
                'details' => 0,
                'nosort' => 0,
                'refresh' => 14400,
            ]]],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRssNotMatched()
    {
        $this->P->parse('Foo {{rss>}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo {{rss>}} Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
