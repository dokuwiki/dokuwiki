<?php

namespace easywiki\test\Subscriptions;

use easywiki\Subscriptions\SubscriberManager;
use EasyWikiTest;

class SubscriberManagerTest extends EasyWikiTest
{
    private $originalSubscriptionConfig;

    public function setUp() : void
    {
        parent::setUp();
        global $conf;
        $this->originalSubscriptionConfig = $conf['subscribers'];
        $conf['subscribers'] = true;
    }

    protected function tearDown() : void
    {
        global $conf;
        $conf['subscribers'] = $this->originalSubscriptionConfig;
        parent::tearDown();
    }

    public function testAddremove()
    {
        $sub = new SubscriberManager();

        // no subscriptions
        $this->assertArrayNotHasKey(
            'wiki:easywiki',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // add page subscription
        $sub->add('wiki:easywiki', 'testuser', 'every');

        // one subscription
        $this->assertArrayHasKey(
            'wiki:easywiki',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // remove page subscription
        $sub->remove('wiki:easywiki', 'testuser');

        // no subscription
        $this->assertArrayNotHasKey(
            'wiki:easywiki',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // add namespace subscription
        $sub->add('wiki:', 'testuser', 'every');

        // one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // remove (non existing) page subscription
        $sub->remove('wiki:easywiki', 'testuser');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // change namespace subscription
        $sub->add('wiki:', 'testuser', 'digest', '1234567');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // check contents
        $this->assertEquals(
            ['wiki:' => ['testuser' => ['digest', '1234567']]],
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // change subscription data
        $sub->add('wiki:', 'testuser', 'digest', '7654321');

        // still one subscription
        $this->assertArrayHasKey(
            'wiki:',
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );

        // check contents
        $this->assertEquals(
            ['wiki:' => ['testuser' => ['digest', '7654321']]],
            $sub->subscribers('wiki:easywiki', null, ['every', 'list', 'digest'])
        );
    }

    /**
     * Tests, if overwriting subscriptions works even when subscriptions for the same
     * user exist for two nested namespaces, this is a test for the bug described in FS#2580
     */
    public function testOverwrite()
    {
        $sub = new SubscriberManager();

        $sub->add(':', 'admin', 'digest', '123456789');
        $sub->add(':wiki:', 'admin', 'digest', '123456789');
        $sub->add(':', 'admin', 'digest', '1234');
        $sub->add(':wiki:', 'admin', 'digest', '1234');

        $subscriptions = $sub->subscribers(':wiki:', 'admin');

        $this->assertCount(
            1,
            $subscriptions[':'],
            'More than one subscription saved for the root namespace even though the old one should have been overwritten.'
        );
        $this->assertCount(
            1,
            $subscriptions[':wiki:'],
            'More than one subscription saved for the wiki namespace even though the old one should have been overwritten.'
        );
        $this->assertCount(2, $subscriptions, 'Didn\'t find the expected two subscriptions');
    }
}
