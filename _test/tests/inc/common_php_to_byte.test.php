<?php

class common_php_to_byte_test extends DokuWikiTest {


    public function data() {
        $data = [
            ['1G', 1073741824],
            ['8M', 8388608],
            ['8K', 8192],
            ['800', 800],
            ['8', 8],
            ['0', 0],
            ['-1', -1]
        ];

        // larger sizes only work on 64bit platforms
        if(PHP_INT_SIZE == 8) {
            $data[] = ['8G', 8589934592];
        }

        return $data;
    }

    /**
     * @dataProvider data
     * @param string $value
     * @param int $bytes
     */
    public function test_undefined($value, $bytes) {
        $this->assertSame($bytes, php_to_byte($value));
    }

}
