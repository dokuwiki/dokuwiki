<?php

/**
 * Tests for the $upscale option of the image resize helpers.
 *
 * Fixture wiki:dokuwiki-128.png is a 128x128 PNG.
 */
class media_resize_test extends DokuWikiTest {

    /** @var string */
    protected $img = 'wiki:dokuwiki-128.png';

    public function test_resize_no_upscale_keeps_original_size() {
        $file = mediaFN($this->img);
        // 128x128 fit into a 500x500 box with upscaling disabled -> stays 128x128
        $out = media_resize_image($file, 'png', 500, 500, false);
        $this->assertNotEquals($file, $out, 'a cache file should have been produced');
        $this->assertFileExists($out);
        [$w, $h] = getimagesize($out);
        $this->assertSame(128, $w);
        $this->assertSame(128, $h);
    }

    public function test_resize_default_still_upscales() {
        $file = mediaFN($this->img);
        // default behaviour (in-page images) still enlarges as before
        $out = media_resize_image($file, 'png', 500, 500);
        $this->assertFileExists($out);
        [$w, $h] = getimagesize($out);
        $this->assertSame(500, $w);
        $this->assertSame(500, $h);
    }

    public function test_upscale_and_no_upscale_use_separate_caches() {
        $file = mediaFN($this->img);
        $up = media_resize_image($file, 'png', 500, 500);
        $noup = media_resize_image($file, 'png', 500, 500, false);
        $this->assertNotEquals($up, $noup, 'the two variants must not share a cache file');
    }
}
