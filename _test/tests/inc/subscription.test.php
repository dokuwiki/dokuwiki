<?php

class subscription_test extends DokuWikiTest {

    function test_regexp() {
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

        $sub = new MockupSubscription();

        $row = 0;
        foreach($tests as $test) {
            $re = $sub->buildregex($test[0], $test[1], $test[2]);
            $this->assertFalse(empty($re), "test line $row");
            $result = preg_grep($re, $data);
            $this->assertEquals($test[3], count($result), "test line $row. $re got\n".print_r($result, true));

            $row++;
        }
    }

    function test_addremove() {
        $sub = new MockupSubscription();

        // no subscriptions
        $this->assertArrayNotHasKey(
            'wiki:dokuwiki',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // add page subscription
        $sub->add('wiki:dokuwiki', 'testuser', 'every');

        // one subscription
        $this->assertArrayHasKey(
            'wiki:dokuwiki',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // remove page subscription
        $sub->remove('wiki:dokuwiki', 'testuser');

        // no subscription
        $this->assertArrayNotHasKey(
            'wiki:dokuwiki',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // add namespace subscription
        $sub->add('wiki:', 'testuser', 'every');

        // one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // remove (non existing) page subscription
        $sub->remove('wiki:dokuwiki', 'testuser');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // change namespace subscription
        $sub->add('wiki:', 'testuser', 'digest', '1234567');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // check contents
        $this->assertEquals(
            array('wiki:' => array('testuser' => array('digest', '1234567'))),
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // change subscription data
        $sub->add('wiki:', 'testuser', 'digest', '7654321');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );

        // check contents
        $this->assertEquals(
            array('wiki:' => array('testuser' => array('digest', '7654321'))),
            $sub->subscribers('wiki:dokuwiki', null, array('every', 'list', 'digest'))
        );
    }

    function test_bulkdigest() {
        $sub = new MockupSubscription();

        // let's start with nothing
        $this->assertEquals(0, $sub->send_bulk('sub1:test'));

        // create a subscription
        $sub->add('sub1:', 'testuser', 'digest', '978328800'); // last mod 2001-01-01

        // now create change
        $_SERVER['REMOTE_USER'] = 'someguy';
        saveWikiText('sub1:test', 'foo bar', 'a subscription change', false);

        // should trigger a mail
        $this->assertEquals(1, $sub->send_bulk('sub1:test'));
        $this->assertEquals(array('arthur@example.com'), $sub->mails);

        $sub->reset();

        // now create more changes
        $_SERVER['REMOTE_USER'] = 'someguy';
        saveWikiText('sub1:sub2:test', 'foo bar', 'a subscription change', false);
        saveWikiText('sub1:another_test', 'foo bar', 'a subscription change', false);

        // should not trigger a mail, because the subscription time has not been reached, yet
        $this->assertEquals(0, $sub->send_bulk('sub1:test'));
        $this->assertEquals(array(), $sub->mails);

        // reset the subscription time
        $sub->add('sub1:', 'testuser', 'digest', '978328800'); // last mod 2001-01-01

        // we now should get mails for three changes
        $this->assertEquals(3, $sub->send_bulk('sub1:test'));
        $this->assertEquals(array('arthur@example.com', 'arthur@example.com', 'arthur@example.com'), $sub->mails);
    }

    function test_bulklist() {
        $sub = new MockupSubscription();

        // let's start with nothing
        $this->assertEquals(0, $sub->send_bulk('sub1:test'));

        // create a subscription
        $sub->add('sub1:', 'testuser', 'list', '978328800'); // last mod 2001-01-01

        // now create change
        $_SERVER['REMOTE_USER'] = 'someguy';
        saveWikiText('sub1:test', 'foo bar', 'a subscription change', false);

        // should trigger a mail
        $this->assertEquals(1, $sub->send_bulk('sub1:test'));
        $this->assertEquals(array('arthur@example.com'), $sub->mails);

        $sub->reset();

        // now create more changes
        $_SERVER['REMOTE_USER'] = 'someguy';
        saveWikiText('sub1:sub2:test', 'foo bar', 'a subscription change', false);
        saveWikiText('sub1:another_test', 'foo bar', 'a subscription change', false);

        // should not trigger a mail, because the subscription time has not been reached, yet
        $this->assertEquals(0, $sub->send_bulk('sub1:test'));
        $this->assertEquals(array(), $sub->mails);

        // reset the subscription time
        $sub->add('sub1:', 'testuser', 'list', '978328800'); // last mod 2001-01-01

        // we now should get a single mail for all three changes
        $this->assertEquals(1, $sub->send_bulk('sub1:test'));
        $this->assertEquals(array('arthur@example.com'), $sub->mails);
    }

    /**
     * Tests, if overwriting subscriptions works even when subscriptions for the same
     * user exist for two nested namespaces, this is a test for the bug described in FS#2580
     */
    function test_overwrite() {
        $sub = new MockupSubscription();

        $sub->add(':', 'admin', 'digest', '123456789');
        $sub->add(':wiki:', 'admin', 'digest', '123456789');
        $sub->add(':', 'admin', 'digest', '1234');
        $sub->add(':wiki:', 'admin', 'digest', '1234');

        $subscriptions = $sub->subscribers(':wiki:', 'admin');

        $this->assertCount(1, $subscriptions[':'], 'More than one subscription saved for the root namespace even though the old one should have been overwritten.');
        $this->assertCount(1, $subscriptions[':wiki:'], 'More than one subscription saved for the wiki namespace even though the old one should have been overwritten.');
        $this->assertCount(2, $subscriptions, 'Didn\'t find the expected two subscriptions');
    }
}

/**
 * makes protected methods visible for testing
 */
class MockupSubscription extends Subscription {
    public $mails; // we keep sent mails here

    public function __construct() {
        $this->reset();
    }

    /**
     * resets the mail array
     */
    public function reset() {
        $this->mails = array();
    }

    public function isenabled() {
        return true;
    }

    public function buildregex($user = null, $style = null, $data = null) {
        return parent::buildregex($user, $style, $data);
    }

    protected function send($subscriber_mail, $subject, $id, $template, $trep, $hrep = null, $headers = array()) {
        $this->mails[] = $subscriber_mail;
        return true;
    }
}

//Setup VIM: ex: et ts=4 :
