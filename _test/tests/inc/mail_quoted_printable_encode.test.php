<?php

class mail_quotedprintable_encode extends DokuWikiTest {

    function test_simple(){
        $in  = 'hello';
        $out = 'hello';
        $this->assertEquals(mail_quotedprintable_encode($in),$out);
    }

    function test_spaceend(){
        $in  = "hello \nhello";
        $out = "hello=20\nhello";
        $this->assertEquals(mail_quotedprintable_encode($in),$out);
    }

    function test_german_utf8(){
        $in  = 'hello überlänge';
        $out = 'hello =C3=BCberl=C3=A4nge';
        $this->assertEquals(mail_quotedprintable_encode($in),$out);
    }

    function test_wrap(){
        $in  = '123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789';
        $out = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 1234=\n56789 123456789";
        $this->assertEquals(mail_quotedprintable_encode($in,74),$out);
    }

    function test_nowrap(){
        $in  = '123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789';
        $out = '123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789';
        $this->assertEquals(mail_quotedprintable_encode($in,0),$out);
    }

    function test_russian_utf8(){
        $in  = 'Ваш пароль для системы Доку Вики';
        $out = '=D0=92=D0=B0=D1=88 =D0=BF=D0=B0=D1=80=D0=BE=D0=BB=D1=8C =D0=B4=D0=BB=D1=8F =D1=81=D0=B8=D1=81=D1=82=D0=B5=D0=BC=D1=8B =D0=94=D0=BE=D0=BA=D1=83 =D0=92=D0=B8=D0=BA=D0=B8';
        $this->assertEquals(mail_quotedprintable_encode($in,0),$out);
    }
}

//Setup VIM: ex: et ts=4 :
