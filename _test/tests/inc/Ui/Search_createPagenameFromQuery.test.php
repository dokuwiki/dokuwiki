<?php

/**
 * Class Search_createPagenameFromQuery
 *
 */
class Search_createPagenameFromQuery extends DokuWikiTest
{

    /**
     * @return array
     */
    function dataProvider()
    {
        return [
            [
                [
                    'query' => 'foo',
                    'parsed_str' => '(W+:foo)',
                    'parsed_ary' => [0 => 'W+:foo',],
                    'words' => [0 => 'foo',],
                    'highlight' => [0 => 'foo',],
                    'and' => [0 => 'foo',],
                    'phrases' => [],
                    'ns' => [],
                    'notns' => [],
                    'not' => [],
                ],
                ':foo',
                'simple single search word',
            ],
            [
                [
                    'query' => 'foo @wiki',
                    'parsed_str' => '(W+:foo)AND(N+:wiki)',
                    'parsed_ary' => [0 => 'W+:foo', 1 => 'N+:wiki', 2 => 'AND',],
                    'words' => [0 => 'foo',],
                    'highlight' => [0 => 'foo',],
                    'and' => [0 => 'foo',],
                    'ns' => [0 => 'wiki',],
                    'phrases' => [],
                    'notns' => [],
                    'not' => [],
                ],
                ':wiki:foo',
                'simple word limited to a namespace',
            ],
            [
                [
                    'query' => 'foo ^wiki',
                    'parsed_str' => '(W+:foo)ANDNOT(N-:wiki)',
                    'parsed_ary' => [0 => 'W+:foo', 1 => 'N-:wiki', 2 => 'NOT', 3 => 'AND',],
                    'words' => [0 => 'foo',],
                    'highlight' => [0 => 'foo',],
                    'and' => [0 => 'foo',],
                    'notns' => [0 => 'wiki',],
                    'phrases' => [],
                    'ns' => [],
                    'not' => [],
                ],
                ':foo',
                'simple word and excluding a namespace',
            ],
            [
                [
                    'query' => 'foo -bar',
                    'parsed_str' => '(W+:foo)ANDNOT((W-:bar))',
                    'parsed_ary' => [0 => 'W+:foo', 1 => 'W-:bar', 2 => 'NOT', 3 => 'AND',],
                    'words' => [0 => 'foo', 1 => 'bar',],
                    'highlight' => [0 => 'foo',],
                    'and' => [0 => 'foo',],
                    'not' => [0 => 'bar',],
                    'phrases' => [],
                    'ns' => [],
                    'notns' => [],
                ],
                ':foo',
                'one word but not the other',
            ],
            [
                [
                    'query' => 'wiki:foo',
                    'parsed_str' => '((W+:wiki)AND(W+:foo))',
                    'parsed_ary' => [0 => 'W+:wiki', 1 => 'W+:foo', 2 => 'AND',],
                    'words' => [0 => 'wiki', 1 => 'foo',],
                    'highlight' => [0 => 'wiki', 1 => 'foo',],
                    'and' => [0 => 'wiki', 1 => 'foo',],
                    'phrases' => [],
                    'ns' => [],
                    'notns' => [],
                    'not' => [],
                ],
                ':wiki:foo',
                'pageid with colons should result in that pageid',
            ],
            [
                [
                    'query' => 'WiKi:Foo',
                    'parsed_str' => '((W+:wiki)AND(W+:foo))',
                    'parsed_ary' => [0 => 'W+:wiki', 1 => 'W+:foo', 2 => 'AND',],
                    'words' => [0 => 'wiki', 1 => 'foo',],
                    'highlight' => [0 => 'wiki', 1 => 'foo',],
                    'and' => [0 => 'wiki', 1 => 'foo',],
                    'phrases' => [],
                    'ns' => [],
                    'notns' => [],
                    'not' => [],
                ],
                ':wiki:foo',
                'uppercased pageid with colons should result in clean pageid',
            ],
            [
                [
                    'query' => 'Бб:Гг:Rr',
                    'parsed_str' => '((W+:бб)AND(W+:гг)AND(W+:rr))',
                    'parsed_ary' => ['W+:бб', 'AND', 'W+:гг', 'AND', 'W+:rr', 'AND'],
                    'words' => ["бб", "гг", "rr"],
                    'highlight' => ["бб", "гг", "rr"],
                    'and' => ["бб", "гг", "rr"],
                    'phrases' => [],
                    'ns' => [],
                    'notns' => [],
                    'not' => [],
                ],
                ':бб:гг:rr',
                'uppercased utf-8 pageid with colons should result in clean pageid',
            ],
            [
                [
                    'query' => '"wiki:foo"',
                    'parsed_str' => '((W_:wiki)AND(W_:foo)AND(P+:wiki:foo))',
                    'parsed_ary' => [0 => 'W_:wiki', 1 => 'W_:foo', 2 => 'AND', 3 => 'P+:wiki:foo', 4 => 'AND',],
                    'words' => [0 => 'wiki', 1 => 'foo',],
                    'phrases' => [0 => 'wiki:foo',],
                    'highlight' => [0 => 'wiki:foo',],
                    'ns' => [],
                    'notns' => [],
                    'and' => [],
                    'not' => [],
                ],
                ':wiki:foo',
                'pageid with colons and wrapped in double quotes should result in that pageid as well',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $inputParsedQuery
     * @param $expectedPageName
     * @param $msg
     */
    function test_simpleshort($inputParsedQuery, $expectedPageName, $msg)
    {
        $search = new \dokuwiki\Ui\Search([], [], []);

        $actualPageName = $search->createPagenameFromQuery($inputParsedQuery);

        $this->assertEquals($expectedPageName, $actualPageName, $msg);
    }

}


