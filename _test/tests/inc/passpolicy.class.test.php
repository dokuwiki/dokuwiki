<?php

class PassPolicy_test extends DokuWikiTest {

    public function newPolicy($minl, $minp, $lower, $upper, $num, $special, $ucheck, $pron=true) {
        $policy                = new PassPolicy();
        $policy->min_pools     = $minp;
        $policy->min_length    = $minl;
        $policy->usepools      = array(
            'lower'   => $lower,
            'upper'   => $upper,
            'numeric' => $num,
            'special' => $special
        );
        $policy->usernamecheck = $ucheck;
        $policy->pronouncable = $pron;

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

        $policy = $this->newPolicy(6, 1, true, true, true, true, 2);
        $this->assertFalse($policy->checkPolicy('tested','tested'), '1 pool, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
        $this->assertFalse($policy->checkPolicy('tested99!','tested'), '1 pool, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
        $this->assertFalse($policy->checkPolicy('tested','untested'), '1 pool, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
        $this->assertFalse($policy->checkPolicy('tested99!','comptessa'), '1 pool1, user check '.$policy->error);
        $this->assertEquals(PassPolicy::USERNAME_VIOLATION, $policy->error);
    }

    public function test_selfcheck() {
        $policy = $this->newPolicy(6, 4, true, true, true, true, 0, true);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 6, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 6, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(18, 4, true, true, true, true, 0, true);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 18, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 18, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(6, 4, true, true, true, true, 0, false);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 6, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 6, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(18, 4, true, true, true, true, 0, false);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 18, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 18, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(18, 1, false, false, false, true, 0, false);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 18, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 18, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(18, 1, false, false, true, false, 0, false);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 18, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 18, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(18, 1, false, true, false, false, 0, false);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 18, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 18, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";

        $policy = $this->newPolicy(18, 1, true, false, false, false, 0, false);
        $pw1 = $policy->generatePassword('test');
        $pw2 = $policy->generatePassword('test');
        $this->assertNotEquals($pw1, $pw2, 'randomness broken');
        $this->assertTrue(strlen($pw1) >= 18, 'pw too short');
        $this->assertTrue(strlen($pw2) >= 18, 'pw too short');
        $this->assertTrue(utf8_isASCII($pw1), 'pw contains non-ASCII, something went wrong');
        $this->assertTrue(utf8_isASCII($pw2), 'pw contains non-ASCII, something went wrong');

        //echo "\n$pw1\n$pw2\n";
    }
}

