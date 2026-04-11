<?php

class io_createnamespace_test extends DokuWikiTest
{
    /**
     * Test that io_createNamespace throws on excessively deep hierarchies
     */
    public function test_depth_limit()
    {
        $this->expectException(RuntimeException::class);

        // build an ID with 200 segments — well above the 128 limit
        $segments = array_fill(0, 200, 'ns');
        $segments[] = 'file.txt';
        $deep_id = implode(':', $segments);

        io_createNamespace($deep_id, 'media');
    }

    /**
     * Test that io_createNamespace still works for reasonable depths
     */
    public function test_normal_depth()
    {
        global $conf;

        io_createNamespace('a:b:c:file.txt', 'media');

        $path = $conf['mediadir'] . '/a/b/c';
        $this->assertDirectoryExists($path, 'Normal namespace depth should be created');
    }
}
