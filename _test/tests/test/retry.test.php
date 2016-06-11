<?php

/**
 * @group integration
 */
class InttestsRetryTest extends DokuWikiTest {

    /**
     * This test only succeeds after 3 retries
     *
     * @retry 4
     */
    function testRetry() {
        static $run = 0;
        $run++;

        if($run > 3) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

}
