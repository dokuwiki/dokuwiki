<?php

use dokuwiki\PassHash;

/**
 * Class PassHash_test
 *
 * most tests are in auth_password.test.php
 */
class PassHash_test extends DokuWikiTest {

    function test_djangopbkdf2() {
        $ph = new PassHash();
        $knownpasses = array (
            'pbkdf2_sha256$24000$LakQQ2OOTO1v$dmUgz8V7zcpaoBSA3MV76J5a4rzrszF0NpxGx6HRBbE=',
            'pbkdf2_sha256$24000$PXogIZpE4gaK$F/P/L5SRrbb6taOGEr4w6DhxjMzNAj1jEWTPyAUn8WU=',
            'pbkdf2_sha256$24000$vtn5APnhirmB$/jzJXYvm78X8/FCOMhGUmcCy0iWhtk0L1hcBWN1AYZc=',
            'pbkdf2_sha256$24000$meyCtGKrS5Ai$vkMfMzB/yGFKplmXujgtfl3OGR27AwOQmP+YeRP6lbw=',
            'pbkdf2_sha256$24000$M8ecC8zfqLmJ$l6cIa/Od+m56VMm9hJbdPNhTXZykPVbUGGTPx7/VRE4=',
        );
        foreach($knownpasses as $known) {
            $this->assertTrue($ph->verify_hash('P4zzW0rd!', $known));
        }
    }
}
