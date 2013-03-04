<?php

class mail_isvalid extends DokuWikiTest {


    function test1(){
        $tests   = array();

        // our own tests
        $tests[] = array('bugs@php.net',true);
        $tests[] = array('~someone@somewhere.com',true);
        $tests[] = array('no+body.here@somewhere.com.au',true);
        $tests[] = array('username+tag@domain.com',true); // FS#1447
        $tests[] = array("rfc2822+allthesechars_#*!'`/-={}are.legal@somewhere.com.au",true);
        $tests[] = array('_foo@test.com',true); // FS#1049
        $tests[] = array('bugs@php.net1',true); // new ICAN rulez seem to allow this
        $tests[] = array('.bugs@php.net1',false);
        $tests[] = array('bu..gs@php.net',false);
        $tests[] = array('bugs@php..net',false);
        $tests[] = array('bugs@.php.net',false);
        $tests[] = array('bugs@php.net.',false);
        $tests[] = array('bu(g)s@php.net1',false);
        $tests[] = array('bu[g]s@php.net1',false);
        $tests[] = array('somebody@somewhere.museum',true);
        $tests[] = array('somebody@somewhere.travel',true);
        $tests[] = array('root@[2010:fb:fdac::311:2101]',true);
        $tests[] = array('test@example', true); // we allow local addresses

        // tests from http://code.google.com/p/php-email-address-validation/ below

        $tests[] = array('test@example.com', true);
        $tests[] = array('TEST@example.com', true);
        $tests[] = array('1234567890@example.com', true);
        $tests[] = array('test+test@example.com', true);
        $tests[] = array('test-test@example.com', true);
        $tests[] = array('t*est@example.com', true);
        $tests[] = array('+1~1+@example.com', true);
        $tests[] = array('{_test_}@example.com', true);
        $tests[] = array('"[[ test ]]"@example.com', true);
        $tests[] = array('test.test@example.com', true);
        $tests[] = array('test."test"@example.com', true);
        $tests[] = array('"test@test"@example.com', true);
        $tests[] = array('test@123.123.123.123', true);
        $tests[] = array('test@[123.123.123.123]', true);
        $tests[] = array('test@example.example.com', true);
        $tests[] = array('test@example.example.example.com', true);

        $tests[] = array('test.example.com', false);
        $tests[] = array('test.@example.com', false);
        $tests[] = array('test..test@example.com', false);
        $tests[] = array('.test@example.com', false);
        $tests[] = array('test@test@example.com', false);
        $tests[] = array('test@@example.com', false);
        $tests[] = array('-- test --@example.com', false); // No spaces allowed in local part
        $tests[] = array('[test]@example.com', false); // Square brackets only allowed within quotes
        $tests[] = array('"test\test"@example.com', false); // Quotes cannot contain backslash
        $tests[] = array('"test"test"@example.com', false); // Quotes cannot be nested
        $tests[] = array('()[]\;:,<>@example.com', false); // Disallowed Characters
        $tests[] = array('test@.', false);
        $tests[] = array('test@example.', false);
        $tests[] = array('test@.org', false);
        $tests[] = array('12345678901234567890123456789012345678901234567890123456789012345@example.com', false); // 64 characters is maximum length for local part. This is 65.
        $tests[] = array('test@123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012.com', false); // 255 characters is maximum length for domain. This is 256.
        $tests[] = array('test@[123.123.123.123', false);
        $tests[] = array('test@123.123.123.123]', false);


        foreach($tests as $test){
            $info = 'Testing '.$test[0];

            if($test[1]){
                $this->assertTrue((bool) mail_isvalid($test[0]), $info);
            }else{
                $this->assertFalse((bool) mail_isvalid($test[0]), $info);
            }
        }
    }

}
//Setup VIM: ex: et ts=4 :
