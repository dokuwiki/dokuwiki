<?php

class subscription_test extends DokuWikiTest {

    function test_regexp(){
        // data to test against
        $data = array(
            "casper every\n",
            "Andreas digest 1344689733",
            "Cold%20Fusion every",
            "zioth list 1344691369\n",
            "nlights digest",
            "rikblok\tdigest  \t 1344716803",
        );

        // user, style, data, expected number of results
        $tests = array(
            array('Cold Fusion', null, null, 1),
            array('casper', null, null, 1),
            array('nope', null, null, 0),
            array('lights', null, null, 0),
            array(array('Cold Fusion','casper','nope'), null, null, 2),
            array(null, 'list', null, 1),
            array(null, 'every', null, 2),
            array(null, 'digest', null, 3),
            array(null, array('list', 'every'), null, 3),
            array('casper', 'digest', null, 0),
            array('casper', array('digest','every'), null, 1),
            array('zioth', 'list', '1344691369', 1),
            array('zioth', null, '1344691369', 1),
            array('zioth', 'digest', '1344691369', 0),
        );

        $sub = new MockupSubscription();

        $row = 0;
        foreach($tests as $test){
            $re = $sub->buildregex($test[0], $test[1], $test[2]);
            $this->assertFalse(empty($re), "test line $row");
            $result = preg_grep($re, $data);
            $this->assertEquals($test[3], count($result), "test line $row. $re got\n".print_r($result, true));

            $row++;
        }
    }

    function test_addremove(){
        $sub = new MockupSubscription();

        // no subscriptions
        $this->assertArrayNotHasKey(
            'wiki:dokuwiki',
            $sub->subscribers('wiki:dokuwiki', null, array('every','list','digest'))
        );

        // add page subscription
        $sub->add('wiki:dokuwiki', 'testuser', 'every');

        // one subscription
        $this->assertArrayHasKey(
            'wiki:dokuwiki',
            $sub->subscribers('wiki:dokuwiki', null, array('every','list','digest'))
        );

        // remove page subscription
        $sub->remove('wiki:dokuwiki', 'testuser');

        // no subscription
        $this->assertArrayNotHasKey(
            'wiki:dokuwiki',
            $sub->subscribers('wiki:dokuwiki', null, array('every','list','digest'))
        );

        // add namespace subscription
        $sub->add('wiki:', 'testuser', 'every');

        // one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every','list','digest'))
        );

        // remove (non existing) page subscription
        $sub->remove('wiki:dokuwiki', 'testuser');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every','list','digest'))
        );

        // change namespace subscription
        $sub->add('wiki:', 'testuser', 'digest', '1234567');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every','list','digest'))
        );

        // check contents
        $this->assertEquals(
            array('wiki:' => array('testuser' => array('digest', '1234567'))),
            $sub->subscribers('wiki:dokuwiki',  null, array('every','list','digest'))
        );
    }



}

/**
 * makes protected methods visible for testing
 */
class MockupSubscription extends Subscription {
     public function buildregex($user = null, $style = null, $data = null) {
         return parent::buildregex($user, $style, $data);
     }
}

//Setup VIM: ex: et ts=4 :
