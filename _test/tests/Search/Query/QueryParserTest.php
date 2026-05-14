<?php

namespace dokuwiki\test\Search\Query;

use dokuwiki\Search\Query\QueryParser;

/**
 * Test cases for the QueryParser
 *
 * @author Michael Große <grosse@cosmocode.de>
 */
class QueryParserTest extends \DokuWikiTest
{
    public function testConvert()
    {
        $inputQuery = 'test -baz "foo bar" @abc ^def';

        $actualParsedQuery = (new QueryParser)->convert($inputQuery);

        $expectedParsedQuery = [
            'query' => 'test -baz "foo bar" @abc ^def',
            'parsed_str' => '(W+:test)ANDNOT((W-:baz))AND((W_:foo)AND(W_:bar)AND(P+:foo bar))AND(N+:abc)ANDNOT(N-:def)',
            'parsed_ary' => [
                'W+:test',
                'W-:baz',
                'NOT',
                'AND',
                'W_:foo',
                'W_:bar',
                'AND',
                'P+:foo bar',
                'AND',
                'AND',
                'N+:abc',
                'AND',
                'N-:def',
                'NOT',
                'AND',
            ],
            'words' => [
                'test',
                'baz',
                'foo',
                'bar',
            ],
            'highlight' => [
                'test',
                'foo bar',
            ],
            'and' => [
                'test',
            ],
            'phrases' => [
                'foo bar',
            ],
            'ns' => [
                'abc',
            ],
            'notns' => [
                'def',
            ],
            'not' => [
                'baz',
            ],
        ];
        $this->assertEquals($expectedParsedQuery, $actualParsedQuery);
    }

    public function testRevert()
    {
        $actualQuery = (new QueryParser)->revert(
            ['test'],
            ['baz'],
            ['foo bar'],
            ['abc'],
            ['def']
        );

        $this->assertEquals('test -baz "foo bar" @abc ^def', $actualQuery);
    }
}
