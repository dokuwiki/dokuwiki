<?php
require_once 'parser.inc.php';

class TestOfDoku_Parser_Replacements extends TestOfDoku_Parser {
    
    function TestOfDoku_Parser_Replacements() {
        $this->UnitTestCase('TestOfDoku_Parser_Replacements');
    }
    
    
    function testSingleAcronym() {
        $this->P->addMode('acronym',new Doku_Parser_Mode_Acronym(array('FOOBAR')));
        $this->P->parse('abc FOOBAR xyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('acronym',array('FOOBAR')),
            array('cdata',array(' xyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testAlmostAnAcronym() {
        $this->P->addMode('acronym',new Doku_Parser_Mode_Acronym(array('FOOBAR')));
        $this->P->parse('abcFOOBARxyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abcFOOBARxyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testMultipleAcronyms() {
        $this->P->addMode('acronym',new Doku_Parser_Mode_Acronym(array('FOO','BAR')));
        $this->P->parse('abc FOO def BAR xyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('acronym',array('FOO')),
            array('cdata',array(' def ')),
            array('acronym',array('BAR')),
            array('cdata',array(' xyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
        
    }
    // 
    
    function testSingleSmiley() {
        $this->P->addMode('smiley',new Doku_Parser_Mode_Smiley(array(':-)')));
        $this->P->parse('abc:-)xyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc')),
            array('smiley',array(':-)')),
            array('cdata',array('xyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testMultipleSmileys() {
        $this->P->addMode('smiley',new Doku_Parser_Mode_Smiley(array(':-)','^_^')));
        $this->P->parse('abc:-)x^_^yz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc')),
            array('smiley',array(':-)')),
            array('cdata',array('x')),
            array('smiley',array('^_^')),
            array('cdata',array('yz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
        
    }
    
    function testBackslashSmiley() {
        // This smiley is really :-\\ but escaping makes like interesting
        $this->P->addMode('smiley',new Doku_Parser_Mode_Smiley(array(':-\\\\')));
        $this->P->parse('abc:-\\\xyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc')),
            array('smiley',array(':-\\\\')),
            array('cdata',array('xyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testSingleWordblock() {
        $this->P->addMode('wordblock',new Doku_Parser_Mode_Wordblock(array('CAT')));
        $this->P->parse('abc CAT xyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('wordblock',array('CAT')),
            array('cdata',array(' xyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testWordblockCase() {
        $this->P->addMode('wordblock',new Doku_Parser_Mode_Wordblock(array('CAT')));
        $this->P->parse('abc cat xyz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('wordblock',array('cat')),
            array('cdata',array(' xyz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testMultipleWordblock() {
        $this->P->addMode('wordblock',new Doku_Parser_Mode_Wordblock(array('CAT','dog')));
        $this->P->parse('abc cat x DOG yz');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('wordblock',array('cat')),
            array('cdata',array(' x ')),
            array('wordblock',array('DOG')),
            array('cdata',array(' yz'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testSingleEntity() {
        $this->P->addMode('entity',new Doku_Parser_Mode_Entity(array('->')));
        $this->P->parse('x -> y');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'x ')),
            array('entity',array('->')),
            array('cdata',array(' y'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testMultipleEntities() {
        $this->P->addMode('entity',new Doku_Parser_Mode_Entity(array('->','<-')));
        $this->P->parse('x -> y <- z');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'x ')),
            array('entity',array('->')),
            array('cdata',array(' y ')),
            array('entity',array('<-')),
            array('cdata',array(' z'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testMultiplyEntity() {
        $this->P->addMode('multiplyentity',new Doku_Parser_Mode_MultiplyEntity());
        $this->P->parse('Foo 10x20 Bar');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('multiplyentity',array(10,20)),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testSingleQuoteOpening() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo 'hello Bar");
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('singlequoteopening',array()),
            array('cdata',array('hello Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testSingleQuoteClosing() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo hello' Bar");
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo hello')),
            array('singlequoteclosing',array()),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testSingleQuotes() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse("Foo 'hello' Bar");
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('singlequoteopening',array()),
            array('cdata',array('hello')),
            array('singlequoteclosing',array()),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testDoubleQuoteOpening() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo "hello Bar');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('doublequoteopening',array()),
            array('cdata',array('hello Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testDoubleQuoteClosing() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo hello" Bar');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo hello')),
            array('doublequoteclosing',array()),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testDoubleQuotes() {
        $this->P->addMode('quotes',new Doku_Parser_Mode_Quotes());
        $this->P->parse('Foo "hello" Bar');
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('doublequoteopening',array()),
            array('cdata',array('hello')),
            array('doublequoteclosing',array()),
            array('cdata',array(' Bar'."\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testHR() {
        $this->P->addMode('hr',new Doku_Parser_Mode_HR());
        $this->P->parse("Foo \n ---- \n Bar");
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('hr',array()),
            array('p_open',array()),
            array('cdata',array("\n Bar\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
    
    function testHREol() {
        $this->P->addMode('hr',new Doku_Parser_Mode_HR());
        $this->P->parse("Foo \n----\n Bar");
        
        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('hr',array()),
            array('p_open',array()),
            array('cdata',array("\n Bar\n")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEqual(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

