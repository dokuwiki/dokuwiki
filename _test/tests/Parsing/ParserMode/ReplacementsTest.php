<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Acronym;
use dokuwiki\Parsing\ParserMode\Entity;
use dokuwiki\Parsing\ParserMode\Hr;
use dokuwiki\Parsing\ParserMode\Multiplyentity;
use dokuwiki\Parsing\ParserMode\Smiley;
use dokuwiki\Parsing\ParserMode\Wordblock;

class ReplacementsTest extends ParserTestBase
{

    function testSingleAcronym() {
        $this->P->addMode('acronym',new Acronym(['FOOBAR']));
        $this->P->parse('abc FOOBAR xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['acronym',['FOOBAR']],
            ['cdata',[' xyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAlmostAnAcronym() {
        $this->P->addMode('acronym',new Acronym(['FOOBAR']));
        $this->P->parse('abcFOOBARxyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abcFOOBARxyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPickAcronymCorrectly() {
        $this->P->addMode('acronym',new Acronym(['FOO']));
        $this->P->parse('FOOBAR FOO');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'FOOBAR ']],
            ['acronym',['FOO']],
            ['cdata',['']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleAcronyms() {
        $this->P->addMode('acronym',new Acronym(['FOO','BAR']));
        $this->P->parse('abc FOO def BAR xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['acronym',['FOO']],
            ['cdata',[' def ']],
            ['acronym',['BAR']],
            ['cdata',[' xyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleAcronymsWithSubset1() {
        $this->P->addMode('acronym',new Acronym(['FOO','A.FOO','FOO.1','A.FOO.1']));
        $this->P->parse('FOO A.FOO FOO.1 A.FOO.1');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n"]],
            ['acronym',['FOO']],
            ['cdata',[" "]],
            ['acronym',['A.FOO']],
            ['cdata',[" "]],
            ['acronym',['FOO.1']],
            ['cdata',[" "]],
            ['acronym',['A.FOO.1']],
            ['cdata',['']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleAcronymsWithSubset2() {
        $this->P->addMode('acronym',new Acronym(['A.FOO.1','FOO.1','A.FOO','FOO']));
        $this->P->parse('FOO A.FOO FOO.1 A.FOO.1');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n"]],
            ['acronym',['FOO']],
            ['cdata',[" "]],
            ['acronym',['A.FOO']],
            ['cdata',[" "]],
            ['acronym',['FOO.1']],
            ['cdata',[" "]],
            ['acronym',['A.FOO.1']],
            ['cdata',['']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleSmileyFail() {
        $this->P->addMode('smiley',new Smiley([':-)']));
        $this->P->parse('abc:-)xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc:-)xyz"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleSmiley() {
        $this->P->addMode('smiley',new Smiley([':-)']));
        $this->P->parse('abc :-) xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['smiley',[':-)']],
            ['cdata',[' xyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleSmileysFail() {
        $this->P->addMode('smiley',new Smiley([':-)','^_^']));
        $this->P->parse('abc:-)x^_^yz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc:-)x^_^yz"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleSmileys() {
        $this->P->addMode('smiley',new Smiley([':-)','^_^']));
        $this->P->parse('abc :-) x ^_^ yz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['smiley',[':-)']],
            ['cdata',[' x ']],
            ['smiley',['^_^']],
            ['cdata',[' yz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testBackslashSmileyFail() {
        $this->P->addMode('smiley',new Smiley([':-\\\\']));
        $this->P->parse('abc:-\\\xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nabc".':-\\\\'."xyz"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testBackslashSmiley() {
        $this->P->addMode('smiley',new Smiley([':-\\\\']));
        $this->P->parse('abc :-\\\ xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['smiley',[':-\\\\']],
            ['cdata',[' xyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleWordblock() {
        $this->P->addMode('wordblock',new Wordblock(['CAT']));
        $this->P->parse('abc CAT xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['wordblock',['CAT']],
            ['cdata',[' xyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWordblockCase() {
        $this->P->addMode('wordblock',new Wordblock(['CAT']));
        $this->P->parse('abc cat xyz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['wordblock',['cat']],
            ['cdata',[' xyz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleWordblock() {
        $this->P->addMode('wordblock',new Wordblock(['CAT','dog']));
        $this->P->parse('abc cat x DOG yz');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'abc ']],
            ['wordblock',['cat']],
            ['cdata',[' x ']],
            ['wordblock',['DOG']],
            ['cdata',[' yz']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleEntity() {
        $this->P->addMode('entity',new Entity(['->']));
        $this->P->parse('x -> y');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'x ']],
            ['entity',['->']],
            ['cdata',[' y']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleEntities() {
        $this->P->addMode('entity',new Entity(['->','<-']));
        $this->P->parse('x -> y <- z');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'x ']],
            ['entity',['->']],
            ['cdata',[' y ']],
            ['entity',['<-']],
            ['cdata',[' z']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultiplyEntity() {
        $this->P->addMode('multiplyentity',new Multiplyentity());
        $this->P->parse('Foo 10x20 Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['multiplyentity',[10,20]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultiplyEntityHex() {
        $this->P->addMode('multiplyentity',new Multiplyentity());
        $this->P->parse('Foo 0x123 Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo 0x123 Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHR() {
        $this->P->addMode('hr',new Hr());
        $this->P->parse("Foo \n ---- \n Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['hr',[]],
            ['p_open',[]],
            ['cdata',["\n Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHREol() {
        $this->P->addMode('hr',new Hr());
        $this->P->parse("Foo \n----\n Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['p_close',[]],
            ['hr',[]],
            ['p_open',[]],
            ['cdata',["\n Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
