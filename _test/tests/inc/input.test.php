<?php

/**
 * Tests for the Input class
 */
class input_test extends DokuWikiTest {

    private $data = array(
        'array'  => array('foo', 'bar'),
        'string' => 'foo',
        'int'    => '17',
        'zero'   => '0',
        'one'    => '1',
        'empty'  => '',
        'emptya' => array(),
        'do'     => array('save' => 'Speichern'),

    );

    /**
     * custom filter function
     *
     * @param $string
     * @return mixed
     */
    public function myfilter($string) {
        $string = str_replace('foo', 'bar', $string);
        $string = str_replace('baz', '', $string);
        return $string;
    }

    public function test_filter() {
        $_GET     = array(
            'foo'    => 'foo',
            'zstring'=> "foo\0bar",
            'znull'  => "\0",
            'zint'   => '42'."\0".'42',
            'zintbaz'=> "baz42",
        );
        $_POST    = $_GET;
        $_REQUEST = $_GET;
        $INPUT    = new Input();

        $filter = array($this,'myfilter');

        $this->assertNotSame('foobar', $INPUT->str('zstring'));
        $this->assertSame('foobar', $INPUT->filter()->str('zstring'));
        $this->assertSame('bar', $INPUT->filter($filter)->str('foo'));
        $this->assertSame('bar', $INPUT->filter()->str('znull', 'bar', true));
        $this->assertNotSame('foobar', $INPUT->str('zstring')); // make sure original input is unmodified

        $this->assertNotSame('foobar', $INPUT->get->str('zstring'));
        $this->assertSame('foobar', $INPUT->get->filter()->str('zstring'));
        $this->assertSame('bar', $INPUT->get->filter($filter)->str('foo'));
        $this->assertSame('bar', $INPUT->get->filter()->str('znull', 'bar', true));
        $this->assertNotSame('foobar', $INPUT->get->str('zstring')); // make sure original input is unmodified

        $this->assertNotSame(4242, $INPUT->int('zint'));
        $this->assertSame(4242, $INPUT->filter()->int('zint'));
        $this->assertSame(42, $INPUT->filter($filter)->int('zintbaz'));
        $this->assertSame(42, $INPUT->filter()->str('znull', 42, true));

        $this->assertSame(true, $INPUT->bool('znull'));
        $this->assertSame(false, $INPUT->filter()->bool('znull'));

        $this->assertSame('foobar', $INPUT->filter()->valid('zstring', array('foobar', 'bang')));
    }

    public function test_str() {
        $_REQUEST      = $this->data;
        $_POST         = $this->data;
        $_GET          = $this->data;
        $_GET['get']   = 1;
        $_POST['post'] = 1;
        $INPUT         = new Input();

        $this->assertSame('foo', $INPUT->str('string'));
        $this->assertSame('', $INPUT->str('none'));
        $this->assertSame('', $INPUT->str('empty'));
        $this->assertSame('foo', $INPUT->str('none', 'foo'));
        $this->assertSame('', $INPUT->str('empty', 'foo'));
        $this->assertSame('foo', $INPUT->str('empty', 'foo', true));

        $this->assertSame(false, $INPUT->str('get', false));
        $this->assertSame(false, $INPUT->str('post', false));

        $this->assertSame('foo', $INPUT->post->str('string'));
        $this->assertSame('', $INPUT->post->str('none'));
        $this->assertSame('', $INPUT->post->str('empty'));
        $this->assertSame('foo', $INPUT->post->str('none', 'foo'));
        $this->assertSame('', $INPUT->post->str('empty', 'foo'));
        $this->assertSame('foo', $INPUT->post->str('empty', 'foo', true));

        $this->assertSame(false, $INPUT->post->str('get', false));
        $this->assertSame('1', $INPUT->post->str('post', false));

        $this->assertSame('foo', $INPUT->get->str('string'));
        $this->assertSame('', $INPUT->get->str('none'));
        $this->assertSame('', $INPUT->get->str('empty'));
        $this->assertSame('foo', $INPUT->get->str('none', 'foo'));
        $this->assertSame('', $INPUT->get->str('empty', 'foo'));
        $this->assertSame('foo', $INPUT->get->str('empty', 'foo', true));

        $this->assertSame(false, $INPUT->get->str('post', false));
        $this->assertSame('1', $INPUT->get->str('get', false));

        $this->assertSame('', $INPUT->str('array'));
    }

    public function test_int() {
        $_REQUEST      = $this->data;
        $_POST         = $this->data;
        $_GET          = $this->data;
        $_GET['get']   = 1;
        $_POST['post'] = 1;
        $INPUT         = new Input();

        $this->assertSame(17, $INPUT->int('int'));
        $this->assertSame(0, $INPUT->int('none'));
        $this->assertSame(0, $INPUT->int('empty'));
        $this->assertSame(42, $INPUT->int('none', 42));
        $this->assertSame(0, $INPUT->int('zero', 42));
        $this->assertSame(42, $INPUT->int('zero', 42, true));

        $this->assertSame(false, $INPUT->int('get', false));
        $this->assertSame(false, $INPUT->int('post', false));

        $this->assertSame(17, $INPUT->post->int('int'));
        $this->assertSame(0, $INPUT->post->int('none'));
        $this->assertSame(0, $INPUT->post->int('empty'));
        $this->assertSame(42, $INPUT->post->int('none', 42));
        $this->assertSame(0, $INPUT->post->int('zero', 42));
        $this->assertSame(42, $INPUT->post->int('zero', 42, true));

        $this->assertSame(false, $INPUT->post->int('get', false));
        $this->assertSame(1, $INPUT->post->int('post', false));

        $this->assertSame(17, $INPUT->post->int('int'));
        $this->assertSame(0, $INPUT->post->int('none'));
        $this->assertSame(0, $INPUT->post->int('empty'));
        $this->assertSame(42, $INPUT->post->int('none', 42));
        $this->assertSame(0, $INPUT->post->int('zero', 42));
        $this->assertSame(42, $INPUT->post->int('zero', 42, true));

        $this->assertSame(false, $INPUT->get->int('post', false));
        $this->assertSame(1, $INPUT->get->int('get', false));

        $this->assertSame(0, $INPUT->int('array'));

        $this->assertSame(0, $INPUT->int('zero', -1));
        $this->assertSame(-1, $INPUT->int('empty', -1));
        $this->assertSame(-1, $INPUT->int('zero', -1, true));
        $this->assertSame(-1, $INPUT->int('empty', -1, true));
    }

    public function test_arr() {
        $_REQUEST      = $this->data;
        $_POST         = $this->data;
        $_GET          = $this->data;
        $_GET['get']   = array(1, 2);
        $_POST['post'] = array(1, 2);
        $INPUT         = new Input();

        $this->assertSame(array('foo', 'bar'), $INPUT->arr('array'));
        $this->assertSame(array(), $INPUT->arr('none'));
        $this->assertSame(array(), $INPUT->arr('empty'));
        $this->assertSame(array(1, 2), $INPUT->arr('none', array(1, 2)));
        $this->assertSame(array(), $INPUT->arr('emptya', array(1, 2)));
        $this->assertSame(array(1, 2), $INPUT->arr('emptya', array(1, 2), true));

        $this->assertSame(false, $INPUT->arr('get', false));
        $this->assertSame(false, $INPUT->arr('post', false));

        $this->assertSame(array('foo', 'bar'), $INPUT->post->arr('array'));
        $this->assertSame(array(), $INPUT->post->arr('none'));
        $this->assertSame(array(), $INPUT->post->arr('empty'));
        $this->assertSame(array(1, 2), $INPUT->post->arr('none', array(1, 2)));
        $this->assertSame(array(), $INPUT->post->arr('emptya', array(1, 2)));
        $this->assertSame(array(1, 2), $INPUT->post->arr('emptya', array(1, 2), true));

        $this->assertSame(false, $INPUT->post->arr('get', false));
        $this->assertSame(array(1, 2), $INPUT->post->arr('post', false));

        $this->assertSame(array('foo', 'bar'), $INPUT->get->arr('array'));
        $this->assertSame(array(), $INPUT->get->arr('none'));
        $this->assertSame(array(), $INPUT->get->arr('empty'));
        $this->assertSame(array(1, 2), $INPUT->get->arr('none', array(1, 2)));
        $this->assertSame(array(), $INPUT->get->arr('emptya', array(1, 2)));
        $this->assertSame(array(1, 2), $INPUT->get->arr('emptya', array(1, 2), true));

        $this->assertSame(array(1, 2), $INPUT->get->arr('get', false));
        $this->assertSame(false, $INPUT->get->arr('post', false));
    }

    public function test_bool() {
        $_REQUEST      = $this->data;
        $_POST         = $this->data;
        $_GET          = $this->data;
        $_GET['get']   = '1';
        $_POST['post'] = '1';
        $INPUT         = new Input();

        $this->assertSame(true, $INPUT->bool('one'));
        $this->assertSame(false, $INPUT->bool('zero'));

        $this->assertSame(false, $INPUT->bool('get'));
        $this->assertSame(false, $INPUT->bool('post'));

        $this->assertSame(true, $INPUT->post->bool('one'));
        $this->assertSame(false, $INPUT->post->bool('zero'));

        $this->assertSame(false, $INPUT->post->bool('get'));
        $this->assertSame(true, $INPUT->post->bool('post'));

        $this->assertSame(false, $INPUT->bool('zero', -1));
        $this->assertSame(-1, $INPUT->bool('empty', -1));
        $this->assertSame(-1, $INPUT->bool('zero', -1, true));
        $this->assertSame(-1, $INPUT->bool('empty', -1, true));
    }

    public function test_remove() {
        $_REQUEST = $this->data;
        $_POST    = $this->data;
        $_GET     = $this->data;
        $INPUT    = new Input();

        $INPUT->remove('string');
        $this->assertNull($_REQUEST['string']);
        $this->assertNull($_POST['string']);
        $this->assertNull($_GET['string']);

        $INPUT->post->remove('int');
        $this->assertNull($_POST['int']);
        $this->assertEquals(17, $_GET['int']);
        $this->assertEquals(17, $_REQUEST['int']);
    }

    public function test_set(){
        $_REQUEST = $this->data;
        $_POST    = $this->data;
        $_GET     = $this->data;
        $INPUT    = new Input();

        $INPUT->set('test','foo');
        $this->assertEquals('foo',$_REQUEST['test']);
        $this->assertNull($_POST['test']);
        $this->assertNull($_GET['test']);

        $INPUT->get->set('test2','foo');
        $this->assertEquals('foo',$_GET['test2']);
        $this->assertEquals('foo',$_REQUEST['test2']);
        $this->assertNull($_POST['test']);
    }

    public function test_ref(){
        $_REQUEST = $this->data;
        $_POST    = $this->data;
        $_GET     = $this->data;
        $INPUT    = new Input();

        $test = &$INPUT->ref('string');
        $this->assertEquals('foo',$test);
        $_REQUEST['string'] = 'bla';
        $this->assertEquals('bla',$test);
    }

    public function test_valid(){
        $_REQUEST = $this->data;
        $_POST    = $this->data;
        $_GET     = $this->data;
        $INPUT    = new Input();

        $valids = array(17, 'foo');
        $this->assertSame(null, $INPUT->valid('nope', $valids));
        $this->assertSame('bang', $INPUT->valid('nope', $valids, 'bang'));
        $this->assertSame(17, $INPUT->valid('int', $valids));
        $this->assertSame('foo', $INPUT->valid('string', $valids));
        $this->assertSame(null, $INPUT->valid('array', $valids));

        $valids = array(true);
        $this->assertSame(true, $INPUT->valid('string', $valids));
        $this->assertSame(true, $INPUT->valid('one', $valids));
        $this->assertSame(null, $INPUT->valid('zero', $valids));
    }

    public function test_extract(){
        $_REQUEST = $this->data;
        $_POST    = $this->data;
        $_GET     = $this->data;
        $INPUT    = new Input();

        $this->assertEquals('save', $INPUT->extract('do')->str('do'));
        $this->assertEquals('', $INPUT->extract('emptya')->str('emptya'));
        $this->assertEquals('foo', $INPUT->extract('string')->str('string'));
        $this->assertEquals('foo', $INPUT->extract('array')->str('array'));

        $this->assertEquals('save', $INPUT->post->extract('do')->str('do'));
        $this->assertEquals('', $INPUT->post->extract('emptya')->str('emptya'));
        $this->assertEquals('foo', $INPUT->post->extract('string')->str('string'));
        $this->assertEquals('foo', $INPUT->post->extract('array')->str('array'));
    }
}
