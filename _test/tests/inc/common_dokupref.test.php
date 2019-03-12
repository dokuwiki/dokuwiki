<?php

class common_dokupref_test extends DokuWikiTest {

    function test_get_default() {
        $this->assertEquals('nil', get_doku_pref('foo', 'nil'));
    }

    function test_set() {
        set_doku_pref('foo1', 'bar1');
        set_doku_pref('foo2', 'bar2');
        $this->assertEquals('bar1', get_doku_pref('foo1', 'nil'));
        $this->assertEquals('bar2', get_doku_pref('foo2', 'nil'));
    }

    function test_set_encode() {
        set_doku_pref('foo#1', 'bar#1');
        set_doku_pref('foo#2', 'bar2');
        $this->assertEquals('bar#1', get_doku_pref('foo#1', 'nil'));
        $this->assertEquals('bar2', get_doku_pref('foo#2', 'nil'));

        set_doku_pref('foo#2', 'bar#2');
        $this->assertEquals('bar#1', get_doku_pref('foo#1', 'nil'));
        $this->assertEquals('bar#2', get_doku_pref('foo#2', 'nil'));
    }

    // mitigate bug in #2721
    function test_duplicate_entries() {
        $_COOKIE['DOKU_PREFS'] = 'foo1#bar1#foo2#bar1#foo2#bar2';
        $this->assertEquals('bar2', get_doku_pref('foo2', 'nil'));

        set_doku_pref('foo2', 'new2');
        $this->assertEquals('bar1', get_doku_pref('foo1', 'nil'));
        $this->assertEquals('new2', get_doku_pref('foo2', 'nil'));
        $this->assertEquals('foo1#bar1#foo2#new2', $_COOKIE['DOKU_PREFS'],
                            'cookie should not have duplicate entries');
    }

    // This is a definition from #1129
    function test_empty() {
        set_doku_pref('foo', '');
        $this->assertSame('', get_doku_pref('foo', 'nil'));

        set_doku_pref('foo', 0);
        $this->assertSame('0', get_doku_pref('foo', 'nil'));

        set_doku_pref('foo', null);
        $this->assertSame('', get_doku_pref('foo', 'nil'));

        set_doku_pref('foo', false);
        $this->assertSame('nil', get_doku_pref('foo', 'nil'));
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
        $this->assertEquals('foo1#bar1#foo2#bar2', $_COOKIE['DOKU_PREFS'],
                            'cookie should not have duplicate entries');
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
