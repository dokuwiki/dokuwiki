<?php

class common_ml_test extends DokuWikiTest {

    private $script = 'lib/exe/fetch.php';

    function test_ml_empty() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;
        $conf['start'] = 'start';

        $this->assertEquals(DOKU_BASE . $this->script . '?media=' , ml());
    }

    function test_ml_args_array() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = array('a' => 'b', 'c' => 'd', 'q' => '&ä');

        $expect = DOKU_BASE . $this->script . '?a=b&amp;c=d&amp;q=%26%C3%A4&amp;media=some:img.jpg';
        $this->assertEquals($expect, ml('some:img.jpg', $args));
    }

    function test_ml_args_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = 'a=b&c=d';

        $expect = DOKU_BASE . $this->script . '?a=b&c=d&amp;media=some:img.png';
        $this->assertEquals($expect, ml('some:img.png', $args));
    }

    function test_ml_args_comma_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = 'a=b,c=d';

        $expect = DOKU_BASE . $this->script . '?a=b&amp;c=d&amp;media=some:img.gif';
        $this->assertEquals($expect, ml('some:img.gif', $args));
    }


    function test_ml_imgresize_array() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $id = 'some:img.png';
        $w = 80;
        $args = array('w' => $w);
        $tok = media_get_token($id,$w,0);

        $expect = DOKU_BASE . $this->script . '?w='.$w.'&amp;tok='.$tok.'&amp;media='.$id;
        $this->assertEquals($expect, ml($id, $args));
    }

    function test_ml_imgresize_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $id = 'some:img.png';
        $w = 80;
        $args = 'w='.$w;
        $tok = media_get_token($id,$w,0);

        $expect = DOKU_BASE . $this->script . '?w='.$w.'&amp;tok='.$tok.'&amp;media='.$id;
        $this->assertEquals($expect, ml($id, $args));
    }

    function test_ml_imgresize_array_rootid() {
        global $conf;
        $conf['useslash']   = 0;
        $conf['userewrite'] = 0;

        $id      = ':wiki:dokuwiki-128.png';
        $cleanid = 'wiki:dokuwiki-128.png';
        $w       = 80;
        $args    = array('w' => $w);
        $tok     = media_get_token($cleanid, $w, 0);

        $expect = DOKU_BASE.$this->script.'?w='.$w.'&amp;tok='.$tok.'&amp;media='.$cleanid;
        $this->assertEquals($expect, ml($id, $args));
    }

    function test_ml_img_external() {
        global $conf;
        $conf['useslash']   = 0;
        $conf['userewrite'] = 0;

        $ids  = array(
            'https://example.com/lib/tpl/dokuwiki/images/logo.png',
            'http://example.com/lib/tpl/dokuwiki/images/logo.png',
            'ftp://example.com/lib/tpl/dokuwiki/images/logo.png'
        );

        foreach($ids as $id) {
            $tok = media_get_token($id, 0, 0);

            $expect = DOKU_BASE.$this->script.'?tok='.$tok.'&amp;media='.rawurlencode($id);
            $this->assertEquals($expect, ml($id));
        }
    }

    function test_ml_imgresize_array_external() {
        global $conf;
        $conf['useslash']   = 0;
        $conf['userewrite'] = 0;

        $ids  = array(
            'https://example.com/lib/tpl/dokuwiki/images/logo.png',
            'http://example.com/lib/tpl/dokuwiki/images/logo.png',
            'ftp://example.com/lib/tpl/dokuwiki/images/logo.png'
        );
        $w    = 80;
        $args = array('w' => $w);

        foreach($ids as $id) {
            $tok = media_get_token($id, $w, 0);
            $hash = substr(PassHash::hmac('md5', $id, auth_cookiesalt()), 0, 6);

            $expect = DOKU_BASE.$this->script.'?w='.$w.'&amp;tok='.$tok.'&amp;media='.rawurlencode($id);
            $this->assertEquals($expect, ml($id, $args));
        }

        $h    = 50;
        $args = array('h' => $h);
        $tok = media_get_token($id, $h, 0);

        $expect = DOKU_BASE.$this->script.'?h='.$h.'&amp;tok='.$tok.'&amp;media='.rawurlencode($id);
        $this->assertEquals($expect, ml($id, $args));

        $w    = 80;
        $h    = 50;
        $args = array('w' => $w, 'h' => $h);
        $tok = media_get_token($id, $w, $h);

        $expect = DOKU_BASE.$this->script.'?w='.$w.'&amp;h='.$h.'&amp;tok='.$tok.'&amp;media='.rawurlencode($id);
        $this->assertEquals($expect, ml($id, $args));

    }
}
