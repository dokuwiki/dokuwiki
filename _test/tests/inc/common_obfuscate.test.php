<?php

class common_obfuscate_test extends DokuWikiTest {

    function test_none(){
        global $conf;
        $conf['mailguard'] = 'none';
        $this->assertEquals(obfuscate('jon-doe@example.com'), 'jon-doe@example.com');
    }

    function test_hex(){
        global $conf;
        $conf['mailguard'] = 'hex';
        $this->assertEquals(obfuscate('jon-doe@example.com'),
        '&#x6a;&#x6f;&#x6e;&#x2d;&#x64;&#x6f;&#x65;&#x40;&#x65;&#x78;&#x61;&#x6d;&#x70;&#x6c;&#x65;&#x2e;&#x63;&#x6f;&#x6d;');
    }

    function test_visible(){
        global $conf;
        $conf['mailguard'] = 'visible';
        $this->assertEquals(obfuscate('jon-doe@example.com'), 'jon [dash] doe [at] example [dot] com');
    }


}
//Setup VIM: ex: et ts=4 :
