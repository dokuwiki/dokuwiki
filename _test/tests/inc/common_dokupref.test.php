<?php

class common_dokupref_test extends DokuWikiTest {

    function test_get_default() {
        $this->assertEquals('nil', get_doku_pref('foo', 'nil'));
    }

    function test_get_empty_string() {
        set_doku_pref('foo', '');
        $this->assertEquals('', get_doku_pref('foo', 'nil'));
    }

    function test_set() {
        set_doku_pref('foo1', 'bar1');
        set_doku_pref('foo2', 'bar2');
        $this->assertEquals('bar1', get_doku_pref('foo1', ''));
        $this->assertEquals('bar2', get_doku_pref('foo2', ''));
    }

    // #2721
    function test_set_empty_string() {
        set_doku_pref('foo1', 'bar1');
        set_doku_pref('foo2', 'bar1');

        set_doku_pref('foo2', '');
        $this->assertEquals('bar1', get_doku_pref('foo1', 'nil'));
        $this->assertEquals('', get_doku_pref('foo2', 'nil'));

        set_doku_pref('foo2', 'bar2');
        $this->assertEquals('bar1', get_doku_pref('foo1', 'nil'));
        $this->assertEquals('bar2', get_doku_pref('foo2', 'nil'));
    }

    // #2721
    function test_set_delete() {
        set_doku_pref('foo1', 'bar1');
        set_doku_pref('foo2', 'bar2');

        set_doku_pref('foo1', false);
        $this->assertEquals('nil', get_doku_pref('foo1', 'nil'));
        $this->assertEquals('bar2', get_doku_pref('foo2', 'nil'));

        set_doku_pref('foo2', false);
        $this->assertEquals('nil', get_doku_pref('foo1', 'nil'));
        $this->assertEquals('nil', get_doku_pref('foo2', 'nil'));
    }

}

//Setup VIM: ex: et ts=4 :
