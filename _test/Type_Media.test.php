<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\types\Media;

/**
 * Testing the Media Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Media_struct_test extends StructTest {

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateFailProvider() {
        return array(
            array('image/jpeg, image/png', 'foo.gif'),
            array('image/jpeg, image/png', 'http://www.example.com/foo.gif'),
            array('application/octet-stream', 'hey:joe.jpeg'),
            array('application/octet-stream', 'http://www.example.com/hey:joe.jpeg'),
        );
    }

    /**
     * Provides successful validation data
     *
     * @return array
     */
    public function validateSuccessProvider() {
        return array(
            array('', 'foo.png'),
            array('', 'http://www.example.com/foo.png'),
            array('image/jpeg, image/png', 'foo.png'),
            array('image/jpeg, image/png', 'http://www.example.com/foo.png'),
            array('image/jpeg, image/png', 'http://www.example.com/dynamic?.png'),
            array('application/octet-stream', 'hey:joe.exe'),
            array('application/octet-stream', 'http://www.example.com/hey:joe.exe'),

        );
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\ValidationException
     * @dataProvider validateFailProvider
     */
    public function test_validate_fail($mime, $value) {
        $integer = new Media(array('mime' => $mime));
        $integer->validate($value);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function test_validate_success($mime, $value) {
        $integer = new Media(array('mime' => $mime));
        $integer->validate($value);
        $this->assertTrue(true); // we simply check that no exceptions are thrown
    }

    public function test_render_page_img() {
        $R = new \Doku_Renderer_xhtml();

        $media = new Media(array('width' => 150, 'height' => 160, 'agg_width' => 180, 'agg_height' => 190));
        $media->renderValue('foo.png', $R, 'xhtml');
        $pq = \phpQuery::newDocument($R->doc);

        $a = $pq->find('a');
        $img = $pq->find('img');

        $this->assertContains('fetch.php', $a->attr('href')); // direct link goes to fetch
        $this->assertEquals('lightbox', $a->attr('rel')); // lightbox single mode
        $this->assertContains('w=150', $img->attr('src')); // fetch param
        $this->assertEquals(150, $img->attr('width')); // img param
        $this->assertContains('h=160', $img->attr('src')); // fetch param
        $this->assertEquals(160, $img->attr('height')); // img param
    }

    public function test_render_aggregation_img() {
        $R = new \Doku_Renderer_xhtml();
        $R->info['struct_table_hash'] = 'HASH';

        $media = new Media(array('width' => 150, 'height' => 160, 'agg_width' => 180, 'agg_height' => 190));
        $media->renderValue('foo.png', $R, 'xhtml');
        $pq = \phpQuery::newDocument($R->doc);

        $a = $pq->find('a');
        $img = $pq->find('img');

        $this->assertContains('fetch.php', $a->attr('href')); // direct link goes to fetch
        $this->assertEquals('lightbox[gal-HASH]', $a->attr('rel')); // lightbox single mode
        $this->assertContains('w=180', $img->attr('src')); // fetch param
        $this->assertEquals(180, $img->attr('width')); // img param
        $this->assertContains('h=190', $img->attr('src')); // fetch param
        $this->assertEquals(190, $img->attr('height')); // img param
    }

    public function test_render_aggregation_pdf() {
        $R = new \Doku_Renderer_xhtml();

        $media = new Media(array('width' => 150, 'height' => 160, 'agg_width' => 180, 'agg_height' => 190, 'mime' => ''));
        $media->renderValue('foo.pdf', $R, 'xhtml');
        $pq = \phpQuery::newDocument($R->doc);

        $a = $pq->find('a');
        $img = $pq->find('img');

        $this->assertContains('fetch.php', $a->attr('href')); // direct link goes to fetch
        $this->assertTrue($a->hasClass('mediafile')); // it's a media link
        $this->assertEquals('', $a->attr('rel')); // no lightbox
        $this->assertEquals(0, $img->length); // no image
        $this->assertEquals('foo.pdf', $a->text()); // name is link name
    }

    public function test_render_aggregation_video() {
        $R = new \Doku_Renderer_xhtml();

        // local video requires an existing file to be rendered. we fake one
        $fake = mediaFN('foo.mp4');
        touch($fake);

        $media = new Media(array('width' => 150, 'height' => 160, 'agg_width' => 180, 'agg_height' => 190, 'mime' => ''));
        $media->renderValue('foo.mp4', $R, 'xhtml');
        $pq = \phpQuery::newDocument($R->doc);

        $a = $pq->find('a');
        $vid = $pq->find('video');
        $src = $pq->find('source');

        $this->assertContains('fetch.php', $a->attr('href')); // direct link goes to fetch
        $this->assertContains('fetch.php', $src->attr('src')); // direct link goes to fetch
        $this->assertEquals(150, $vid->attr('width')); // video param
        $this->assertEquals(160, $vid->attr('height')); // video param
    }

}
