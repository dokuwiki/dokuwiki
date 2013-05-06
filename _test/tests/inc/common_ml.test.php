<?php

class common_wl_test extends DokuWikiTest {

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

        $expect = DOKU_BASE . $this->script . '?a=b&amp;c=d&amp;q=%26%C3%A4&amp;media=some:';
        $this->assertEquals($expect, ml('some:', $args));
    }

    function test_ml_args_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = 'a=b&c=d';

        $expect = DOKU_BASE . $this->script . '?a=b&c=d&amp;media=some:';
        $this->assertEquals($expect, ml('some:', $args));
    }

    function test_ml_args_comma_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = 'a=b,c=d';

        $expect = DOKU_BASE . $this->script . '?a=b&amp;c=d&amp;media=some:';
        $this->assertEquals($expect, ml('some:', $args));
    }


    function test_ml_imgresize_array() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $id = 'some:';
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

        $id = 'some:';
        $w = 80;
        $args = 'w='.$w;
        $tok = media_get_token($id,$w,0);

        $expect = DOKU_BASE . $this->script . '?w='.$w.'&amp;tok='.$tok.'&amp;media='.$id;
        $this->assertEquals($expect, ml($id, $args));
    }
}