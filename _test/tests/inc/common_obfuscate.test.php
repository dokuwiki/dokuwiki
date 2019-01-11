<?php

class common_obfuscate_test extends DokuWikiTest {

    function test_none(){
        global $conf;
        $conf['mailguard'] = 'none';
        $this->assertEquals('jon-doe@example.com', obfuscate('jon-doe@example.com'));
    }

    function test_hex(){
        global $conf;
        $conf['mailguard'] = 'hex';
        $this->assertEquals('&#106;&#111;&#110;&#45;&#100;&#111;&#101;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;',
                            obfuscate('jon-doe@example.com'));
    }

    function test_hex_utf32(){
        global $conf;
        $conf['mailguard'] = 'hex';
        $this->assertEquals('&#117;&#115;&#101;&#114;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;&#63;&#115;&#117;&#98;&#106;&#101;&#99;&#116;&#61;&#x41f;&#x440;&#x438;&#x432;&#x435;&#x442;',
                            obfuscate('user@example.com?subject=Привет'));
    }

    function test_visible(){
        global $conf;
        $conf['mailguard'] = 'visible';
        $this->assertEquals('jon [dash] doe [at] example [dot] com', obfuscate('jon-doe@example.com'));
    }


}
//Setup VIM: ex: et ts=4 :
