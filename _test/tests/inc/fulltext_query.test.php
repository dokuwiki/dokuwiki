<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

/**
 * Test cases for the link index
 *
 * @author Michael Große <grosse@cosmocode.de>
 *
 * @group  fulltext
 */
class fulltext_query_test extends DokuWikiTest
{
    public function test_parse_query()
    {
        $Indexer = idx_get_indexer();
        $inputQuery = 'test -baz "foo bar" @abc ^def';

        $actualParsedQuery = ft_queryParser($Indexer, $inputQuery);

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

    public function test_unparse_query()
    {
        $input = [
            'and' => [
                'test',
            ],
            'not' => [
                'baz'
            ],
            'phrases' => [
                'foo bar',
            ],
            'ns' => [
                'abc',
            ],
            'notns' => [
                'def'
            ],
        ];

        $actualQuery = ft_queryUnparser_simple(
            $input['and'],
            $input['not'],
            $input['phrases'],
            $input['ns'],
            $input['notns']
        );

        $expectedQuery = 'test -baz "foo bar" @abc ^def';
        $this->assertEquals($expectedQuery, $actualQuery);
    }
}
