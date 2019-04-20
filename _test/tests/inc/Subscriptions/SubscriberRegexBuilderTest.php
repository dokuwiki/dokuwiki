<?php

namespace tests\inc\Subscriptions;

use dokuwiki\Subscriptions\SubscriberRegexBuilder;
use DokuWikiTest;

class SubscriberRegexBuilderTest extends DokuWikiTest
{

    public function regexTestdataProvider()
    {
        return [
            ['Cold Fusion', null, null, 1],
            ['casper', null, null, 1],
            ['nope', null, null, 0],
            ['lights', null, null, 0],
            [['Cold Fusion', 'casper', 'nope'], null, null, 2],
            [null, 'list', null, 1],
            [null, 'every', null, 2],
            [null, 'digest', null, 3],
            [null, ['list', 'every'], null, 3],
            ['casper', 'digest', null, 0],
            ['casper', ['digest', 'every'], null, 1],
            ['zioth', 'list', '1344691369', 1],
            ['zioth', null, '1344691369', 1],
            ['zioth', 'digest', '1344691369', 0],
        ];
    }

    /**
     * @dataProvider regexTestdataProvider
     */
    public function testRegexp($user, $style, $inputData, $expectedNumResults)
    {
        // data to test against
        $data = [
            "casper every\n",
            "Andreas digest 1344689733",
            "Cold%20Fusion every",
            "zioth list 1344691369\n",
            "nlights digest",
            "rikblok\tdigest  \t 1344716803",
        ];

        $sub = new SubscriberRegexBuilder();
        $re = $sub->buildRegex($user, $style, $inputData);
        $this->assertFalse(empty($re));
        $result = preg_grep($re, $data);
        $this->assertEquals($expectedNumResults, count($result));
    }
}
