<?php

class PassPolicy_test extends DokuWikiTest {

    public function newPolicy($minl, $minp, $lower, $upper, $num, $special, $ucheck) {
        $policy                = new MockPassPolicy();
        $policy->min_pools     = $minp;
        $policy->min_length    = $minl;
        $policy->usepools      = array(
            'lower'   => $lower,
            'upper'   => $upper,
            'numeric' => $num,
            'special' => $special
        );
        $policy->usernamecheck = $ucheck;

        return $policy;
    }

    public function test_policies() {
        $policy = $this->newPolicy(6, 1, true, true, true, true, 0);
        $this->assertTrue($policy->checkPolicy('tested','tested'), '1 pool, no user check '.$policy->error);
        $this->assertFalse($policy->checkPolicy('test','tested'), '1 pool, no user check, but too short '.$policy->error);
        $this->assertEquals(PassPolicy::LENGTH_VIOLATION, $policy->error);
        $this->assertTrue($policy->checkPolicy('tested99!','tested'), '1 pool, no user check '.$policy->error);

        $policy = $this->newPolicy(6, 3, true, true, true, true, 0);
        $this->assertFalse($policy->checkPolicy('tested','tested'), '3 pools, no user check '.$policy->error);
        $this->assertEquals(PassPolicy::POOL_VIOLATION, $policy->error);
        $this->assertTrue($policy->checkPolicy('tested99!','tested'), '3 pools, no user check '.$policy->error);

        $policy = $this->newPolicy(6, 1, true, true, true, true, 3);
        $this->assertFalse($policy->checkPolicy('tested','tested'), '1 pool, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
        $this->assertFalse($policy->checkPolicy('tested99!','tested'), '1 pool, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
        $this->assertFalse($policy->checkPolicy('tested','untested'), '1 pool, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
        $this->assertFalse($policy->checkPolicy('tested99!','comptessa'), '1 pool1, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
    }
}

/**
 * Mockup class to make internal functions visible
 */
class MockPassPolicy extends PassPolicy {

    public function pronouncablePassword() {
        return parent::pronouncablePassword();
    }

    public function randomPassword() {
        return parent::randomPassword();
    }

}