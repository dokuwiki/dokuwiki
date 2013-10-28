<?php

/**
 * Tests the random generator functions
 */
class auth_random_test extends DokuWikiTest {
    function testRandomRange() {
        $rand = auth_random(300, 2000);
        $this->assertTrue($rand <= 2000, 'The generated number was above the limit');
        $this->assertTrue($rand >= 300, 'The generate number was too low');
    }

    function testLargeRandoms() {
        $min = (1 << 30);
        $max = $min + (1 << 33) + 17;
        $rand = auth_random($min, $max);
        $this->assertTrue($rand >= $min, 'The generated number was too low');
        $this->assertTrue($rand <= $max, 'The generated number was too high');
    }
}
