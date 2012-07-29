<?php

class utf8_basename_test extends DokuWikiTest {

     function test1(){
        $data = array(
            array('/this/foo/bar.test.png',         '', 'bar.test.png'),
            array('\\this\\foo\\bar.test.png',      '', 'bar.test.png'),
            array('/this\\foo/bar.test.png',        '', 'bar.test.png'),
            array('/this/foo\\bar.test.png',        '', 'bar.test.png'),

            array('/this/ДокуВики/bar.test.png',    '', 'bar.test.png'),
            array('\\this\\ДокуВики\\bar.test.png', '', 'bar.test.png'),
            array('/this\\ДокуВики/bar.test.png',   '', 'bar.test.png'),
            array('/this/ДокуВики\\bar.test.png',   '', 'bar.test.png'),

            array('/this/foo/ДокуВики.test.png',    '', 'ДокуВики.test.png'),
            array('\\this\\foo\\ДокуВики.test.png', '', 'ДокуВики.test.png'),
            array('/this\\foo/ДокуВики.test.png',   '', 'ДокуВики.test.png'),
            array('/this/foo\\ДокуВики.test.png',   '', 'ДокуВики.test.png'),

            array('/this/foo/bar.test.png',         '.png', 'bar.test'),
            array('\\this\\foo\\bar.test.png',      '.png', 'bar.test'),
            array('/this\\foo/bar.test.png',        '.png', 'bar.test'),
            array('/this/foo\\bar.test.png',        '.png', 'bar.test'),

            array('/this/ДокуВики/bar.test.png',    '.png', 'bar.test'),
            array('\\this\\ДокуВики\\bar.test.png', '.png', 'bar.test'),
            array('/this\\ДокуВики/bar.test.png',   '.png', 'bar.test'),
            array('/this/ДокуВики\\bar.test.png',   '.png', 'bar.test'),

            array('/this/foo/ДокуВики.test.png',    '.png', 'ДокуВики.test'),
            array('\\this\\foo\\ДокуВики.test.png', '.png', 'ДокуВики.test'),
            array('/this\\foo/ДокуВики.test.png',   '.png', 'ДокуВики.test'),
            array('/this/foo\\ДокуВики.test.png',   '.png', 'ДокуВики.test'),

            array('/this/foo/bar.test.png',         '.foo', 'bar.test.png'),
            array('\\this\\foo\\bar.test.png',      '.foo', 'bar.test.png'),
            array('/this\\foo/bar.test.png',        '.foo', 'bar.test.png'),
            array('/this/foo\\bar.test.png',        '.foo', 'bar.test.png'),

            array('/this/ДокуВики/bar.test.png',    '.foo', 'bar.test.png'),
            array('\\this\\ДокуВики\\bar.test.png', '.foo', 'bar.test.png'),
            array('/this\\ДокуВики/bar.test.png',   '.foo', 'bar.test.png'),
            array('/this/ДокуВики\\bar.test.png',   '.foo', 'bar.test.png'),

            array('/this/foo/ДокуВики.test.png',    '.foo', 'ДокуВики.test.png'),
            array('\\this\\foo\\ДокуВики.test.png', '.foo', 'ДокуВики.test.png'),
            array('/this\\foo/ДокуВики.test.png',   '.foo', 'ДокуВики.test.png'),
            array('/this/foo\\ДокуВики.test.png',   '.foo', 'ДокуВики.test.png'),


            array('/this/foo/ДокуВики.test.Вик',    '.foo', 'ДокуВики.test.Вик'),
            array('\\this\\foo\\ДокуВики.test.Вик', '.foo', 'ДокуВики.test.Вик'),
            array('/this\\foo/ДокуВики.test.Вик',   '.foo', 'ДокуВики.test.Вик'),
            array('/this/foo\\ДокуВики.test.Вик',   '.foo', 'ДокуВики.test.Вик'),

            array('/this/foo/ДокуВики.test.Вик',    '.Вик', 'ДокуВики.test'),
            array('\\this\\foo\\ДокуВики.test.Вик', '.Вик', 'ДокуВики.test'),
            array('/this\\foo/ДокуВики.test.Вик',   '.Вик', 'ДокуВики.test'),
            array('/this/foo\\ДокуВики.test.Вик',   '.Вик', 'ДокуВики.test'),

            array('bar.test.png', '', 'bar.test.png'),
            array('bar.test.png', '.png', 'bar.test'),

            array('/bar.test.png', '', 'bar.test.png'),
            array('/bar.test.png', '.png', 'bar.test'),
            array('\\bar.test.png', '', 'bar.test.png'),
            array('\\bar.test.png', '.png', 'bar.test'),
            array('\\/bar.test.png', '', 'bar.test.png'),
            array('\\/bar.test.png', '.png', 'bar.test'),
            array('/\\bar.test.png', '', 'bar.test.png'),
            array('/\\bar.test.png', '.png', 'bar.test'),

            // PHP's basename does this too:
            array('foo/',   '', 'foo'),
            array('foo\\',  '', 'foo'),
            array('foo\\/', '', 'foo'),
            array('foo/\\', '', 'foo'),
            array('foo.png/',   '.png', 'foo'),
            array('foo.png\\',  '.png', 'foo'),
            array('foo.png\\/', '.png', 'foo'),
            array('foo.png/\\', '.png', 'foo'),
        );

        foreach($data as $test){
            $this->assertEquals($test[2], utf8_basename($test[0], $test[1]), "input: ('".$test[0]."', '".$test[1]."')");
        }
     }

}