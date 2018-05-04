<?php

use dokuwiki\Parsing\ParserMode\Acronym;
use dokuwiki\Parsing\ParserMode\Entity;
use dokuwiki\Parsing\ParserMode\Hr;
use dokuwiki\Parsing\ParserMode\Multiplyentity;
use dokuwiki\Parsing\ParserMode\Smiley;
use dokuwiki\Parsing\ParserMode\Wordblock;

require_once 'parser.inc.php';

class TestOfDoku_Parser_Replacements extends TestOfDoku_Parser {

    function testSingleAcronym() {
        $this->P->addMode('acronym',new Acronym(array('FOOBAR')));
        $this->P->parse('abc FOOBAR xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('acronym',array('FOOBAR')),
            array('cdata',array(' xyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testAlmostAnAcronym() {
        $this->P->addMode('acronym',new Acronym(array('FOOBAR')));
        $this->P->parse('abcFOOBARxyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abcFOOBARxyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testPickAcronymCorrectly() {
        $this->P->addMode('acronym',new Acronym(array('FOO')));
        $this->P->parse('FOOBAR FOO');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'FOOBAR ')),
            array('acronym',array('FOO')),
            array('cdata',array('')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultipleAcronyms() {
        $this->P->addMode('acronym',new Acronym(array('FOO','BAR')));
        $this->P->parse('abc FOO def BAR xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('acronym',array('FOO')),
            array('cdata',array(' def ')),
            array('acronym',array('BAR')),
            array('cdata',array(' xyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);

    }

    function testMultipleAcronymsWithSubset1() {
        $this->P->addMode('acronym',new Acronym(array('FOO','A.FOO','FOO.1','A.FOO.1')));
        $this->P->parse('FOO A.FOO FOO.1 A.FOO.1');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n")),
            array('acronym',array('FOO')),
            array('cdata',array(" ")),
            array('acronym',array('A.FOO')),
            array('cdata',array(" ")),
            array('acronym',array('FOO.1')),
            array('cdata',array(" ")),
            array('acronym',array('A.FOO.1')),
            array('cdata',array('')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultipleAcronymsWithSubset2() {
        $this->P->addMode('acronym',new Acronym(array('A.FOO.1','FOO.1','A.FOO','FOO')));
        $this->P->parse('FOO A.FOO FOO.1 A.FOO.1');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n")),
            array('acronym',array('FOO')),
            array('cdata',array(" ")),
            array('acronym',array('A.FOO')),
            array('cdata',array(" ")),
            array('acronym',array('FOO.1')),
            array('cdata',array(" ")),
            array('acronym',array('A.FOO.1')),
            array('cdata',array('')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleSmileyFail() {
        $this->P->addMode('smiley',new Smiley(array(':-)')));
        $this->P->parse('abc:-)xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc:-)xyz")),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleSmiley() {
        $this->P->addMode('smiley',new Smiley(array(':-)')));
        $this->P->parse('abc :-) xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('smiley',array(':-)')),
            array('cdata',array(' xyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultipleSmileysFail() {
        $this->P->addMode('smiley',new Smiley(array(':-)','^_^')));
        $this->P->parse('abc:-)x^_^yz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc:-)x^_^yz")),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultipleSmileys() {
        $this->P->addMode('smiley',new Smiley(array(':-)','^_^')));
        $this->P->parse('abc :-) x ^_^ yz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('smiley',array(':-)')),
            array('cdata',array(' x ')),
            array('smiley',array('^_^')),
            array('cdata',array(' yz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testBackslashSmileyFail() {
        // This smiley is really :-\\ but escaping makes like interesting
        $this->P->addMode('smiley',new Smiley(array(':-\\\\')));
        $this->P->parse('abc:-\\\xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\nabc".':-\\\\'."xyz")),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testBackslashSmiley() {
        // This smiley is really :-\\ but escaping makes like interesting
        $this->P->addMode('smiley',new Smiley(array(':-\\\\')));
        $this->P->parse('abc :-\\\ xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('smiley',array(':-\\\\')),
            array('cdata',array(' xyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleWordblock() {
        $this->P->addMode('wordblock',new Wordblock(array('CAT')));
        $this->P->parse('abc CAT xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('wordblock',array('CAT')),
            array('cdata',array(' xyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testWordblockCase() {
        $this->P->addMode('wordblock',new Wordblock(array('CAT')));
        $this->P->parse('abc cat xyz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('wordblock',array('cat')),
            array('cdata',array(' xyz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultipleWordblock() {
        $this->P->addMode('wordblock',new Wordblock(array('CAT','dog')));
        $this->P->parse('abc cat x DOG yz');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'abc ')),
            array('wordblock',array('cat')),
            array('cdata',array(' x ')),
            array('wordblock',array('DOG')),
            array('cdata',array(' yz')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testSingleEntity() {
        $this->P->addMode('entity',new Entity(array('->')));
        $this->P->parse('x -> y');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'x ')),
            array('entity',array('->')),
            array('cdata',array(' y')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultipleEntities() {
        $this->P->addMode('entity',new Entity(array('->','<-')));
        $this->P->parse('x -> y <- z');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'x ')),
            array('entity',array('->')),
            array('cdata',array(' y ')),
            array('entity',array('<-')),
            array('cdata',array(' z')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultiplyEntity() {
        $this->P->addMode('multiplyentity',new Multiplyentity());
        $this->P->parse('Foo 10x20 Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('multiplyentity',array(10,20)),
            array('cdata',array(' Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testMultiplyEntityHex() {
    	// the multiply entity pattern should not match hex numbers, eg. 0x123
        $this->P->addMode('multiplyentity',new Multiplyentity());
        $this->P->parse('Foo 0x123 Bar');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo 0x123 Bar')),
            array('p_close',array()),
            array('document_end',array()),
        );

        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testHR() {
        $this->P->addMode('hr',new Hr());
        $this->P->parse("Foo \n ---- \n Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('hr',array()),
            array('p_open',array()),
            array('cdata',array("\n Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }

    function testHREol() {
        $this->P->addMode('hr',new Hr());
        $this->P->parse("Foo \n----\n Bar");

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('cdata',array("\n".'Foo ')),
            array('p_close',array()),
            array('hr',array()),
            array('p_open',array()),
            array('cdata',array("\n Bar")),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$this->H->calls),$calls);
    }
}

