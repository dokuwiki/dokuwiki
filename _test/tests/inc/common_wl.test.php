<?php

class common_wl_test extends DokuWikiTest {

    function test_wl_empty() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;
        $conf['start'] = 'start';

        $this->assertEquals(DOKU_BASE . DOKU_SCRIPT . '?id=start' , wl());
    }

    function test_wl_empty_rewrite1() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 1;
        $conf['start'] = 'start';

        $this->assertEquals(DOKU_BASE . 'start' , wl());
    }

    function test_wl_empty_rewrite2() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 2;
        $conf['start'] = 'start';

        $this->assertEquals(DOKU_BASE . DOKU_SCRIPT . '/start' , wl());
    }

    function test_wl_id() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some';
        $this->assertEquals($expect, wl('some'));
    }

    function test_wl_id_ns() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:some';
        $this->assertEquals($expect, wl('some:some'));
    }

    function test_wl_id_ns_start() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:';
        $this->assertEquals($expect, wl('some:'));
    }

    function test_wl_args_array() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = array('a' => 'b', 'c' => 'd', 'q' => '&ä');

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:&amp;a=b&amp;c=d&amp;q=%26%C3%A4';
        $this->assertEquals($expect, wl('some:', $args));
    }

    function test_wl_args_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = 'a=b&c=d';

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:&amp;a=b&c=d';
        $this->assertEquals($expect, wl('some:', $args));
    }

    function test_wl_args_comma_string() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $args = 'a=b,c=d';

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:&amp;a=b&amp;c=d';
        $this->assertEquals($expect, wl('some:', $args));
    }

    function test_wl_abs() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $expect = DOKU_URL . DOKU_SCRIPT . '?id=some:';
        $this->assertEquals($expect, wl('some:', '', true));
    }

    function test_wl_sep() {
        global $conf;
        $conf['useslash'] = 0;
        $conf['userewrite'] = 0;

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:&a=b&c=d';
        $this->assertEquals($expect, wl('some:', 'a=b,c=d', false, '&'));
    }

    function test_wl_useslash() {
        global $conf;
        $conf['useslash'] = 1;
        $conf['userewrite'] = 0;

        $expect = DOKU_BASE . DOKU_SCRIPT . '?id=some:&a=b&c=d';
        $this->assertEquals($expect, wl('some:', 'a=b,c=d', false, '&'));
    }

    function test_wl_useslash_rewrite1() {
        global $conf;
        $conf['useslash'] = 1;
        $conf['userewrite'] = 1;

        $expect = DOKU_BASE . 'some/?a=b&c=d';
        $this->assertEquals($expect, wl('some:', 'a=b,c=d', false, '&'));
    }

    function test_wl_useslash_rewrite1_sub_page() {
        global $conf;
        $conf['useslash'] = 1;
        $conf['userewrite'] = 1;

        $expect = DOKU_BASE . 'some/one?a=b&c=d';
        $this->assertEquals($expect, wl('some:one', 'a=b,c=d', false, '&'));
    }

    function test_wl_useslash_rewrite2() {
        global $conf;
        $conf['useslash'] = 1;
        $conf['userewrite'] = 2;

        $expect = DOKU_BASE . DOKU_SCRIPT . '/some/one?a=b&c=d';
        $this->assertEquals($expect, wl('some:one', 'a=b,c=d', false, '&'));
    }



}