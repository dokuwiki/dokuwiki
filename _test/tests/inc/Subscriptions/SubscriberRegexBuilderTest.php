<?php


namespace tests\inc\Subscriptions;


use dokuwiki\Subscriptions\SubscriberRegexBuilder;
use DokuWikiTest;

class SubscriberRegexBuilderTest extends DokuWikiTest
{

    public function regexTestdataProvider() {
        return array(
            array('Cold Fusion', null, null, 1),
            array('casper', null, null, 1),
            array('nope', null, null, 0),
            array('lights', null, null, 0),
            array(array('Cold Fusion', 'casper', 'nope'), null, null, 2),
            array(null, 'list', null, 1),
            array(null, 'every', null, 2),
            array(null, 'digest', null, 3),
            array(null, array('list', 'every'), null, 3),
            array('casper', 'digest', null, 0),
            array('casper', array('digest', 'every'), null, 1),
            array('zioth', 'list', '1344691369', 1),
            array('zioth', null, '1344691369', 1),
            array('zioth', 'digest', '1344691369', 0),
        );
    }

    /**
     * @dataProvider regexTestdataProvider
     */
    public function testRegexp($user, $style, $inputData, $expectedNumResults) {
        // data to test against
        $data = array(
            "casper every\n",
            "Andreas digest 1344689733",
            "Cold%20Fusion every",
            "zioth list 1344691369\n",
            "nlights digest",
            "rikblok\tdigest  \t 1344716803",
        );

        $sub = new SubscriberRegexBuilder();
        $re = $sub->buildRegex($user, $style, $inputData);
        $this->assertFalse(empty($re));
        $result = preg_grep($re, $data);
        $this->assertEquals($expectedNumResults, count($result));
    }
}
