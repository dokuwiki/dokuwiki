<?php
/**
 * Tests the subscription set function
 */
class subscription_set_test extends DokuWikiTest {
    /**
     * Tests, if overwriting subscriptions works even when subscriptions for the same
     * user exist for two nested namespaces, this is a test for the bug described in FS#2580
     */
    function test_overwrite() {
        subscription_set('admin', ':', 'digest', '123456789');
        subscription_set('admin', ':wiki:', 'digest', '123456789');
        subscription_set('admin', ':', 'digest', '1234', true);
        subscription_set('admin', ':wiki:', 'digest', '1234', true);
        $subscriptions = subscription_find(':wiki:', array('user' => 'admin'));
        $this->assertCount(1, $subscriptions[':'], 'More than one subscription saved for the root namespace even though the old one should have been overwritten.');
        $this->assertCount(1, $subscriptions[':wiki:'], 'More than one subscription saved for the wiki namespace even though the old one should have been overwritten.');
        $this->assertCount(2, $subscriptions, 'Didn\'t find the expected two subscriptions');
    }
}
