<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Acronym;
use dokuwiki\Parsing\ParserMode\Deleted;
use dokuwiki\Parsing\ParserMode\Emphasis;
use dokuwiki\Parsing\ParserMode\Header;
use dokuwiki\Parsing\ParserMode\Monospace;
use dokuwiki\Parsing\ParserMode\Strong;
use dokuwiki\Parsing\ParserMode\Subscript;
use dokuwiki\Parsing\ParserMode\Superscript;
use dokuwiki\Parsing\ParserMode\Underline;
use dokuwiki\Parsing\ParserMode\Internallink;
use dokuwiki\Parsing\ParserMode\Table;

class I18nTest extends ParserTestBase
{

    function testFormatting() {
        $formats = [
            'strong'      => new Strong(),
            'emphasis'    => new Emphasis(),
            'underline'   => new Underline(),
            'monospace'   => new Monospace(),
            'subscript'   => new Subscript(),
            'superscript' => new Superscript(),
            'deleted'     => new Deleted(),
        ];
        foreach ($formats as $name => $obj) {
            $this->P->addMode($name, $obj);
        }
        $this->P->parse("I**ñ**t__ë__r//n//â<sup>t</sup>i<sub>ô</sub>n''à''liz<del>æ</del>tiøn");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nI"]],
            ['strong_open',[]],
            ['cdata',['ñ']],
            ['strong_close',[]],
            ['cdata',['t']],
            ['underline_open',[]],
            ['cdata',['ë']],
            ['underline_close',[]],
            ['cdata',['r']],
            ['emphasis_open',[]],
            ['cdata',['n']],
            ['emphasis_close',[]],
            ['cdata',['â']],
            ['superscript_open',[]],
            ['cdata',['t']],
            ['superscript_close',[]],
            ['cdata',['i']],
            ['subscript_open',[]],
            ['cdata',['ô']],
            ['subscript_close',[]],
            ['cdata',['n']],
            ['monospace_open',[]],
            ['cdata',['à']],
            ['monospace_close',[]],
            ['cdata',['liz']],
            ['deleted_open',[]],
            ['cdata',['æ']],
            ['deleted_close',[]],
            ['cdata',["tiøn"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testHeader() {
        $this->P->addMode('header',new Header());
        $this->P->parse("Foo\n ==== Iñtërnâtiônàlizætiøn ==== \n Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nFoo"]],
            ['p_close',[]],
            ['header',['Iñtërnâtiônàlizætiøn',3,5]],
            ['section_open',[3]],
            ['p_open',[]],
            ['cdata',["\n Bar"]],
            ['p_close',[]],
            ['section_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTable() {
        $this->P->addMode('table',new Table());
        $this->P->parse('
abc
| Row 0 Col 1    | Iñtërnâtiônàlizætiøn     | Row 0 Col 3        |
| Row 1 Col 1    | Iñtërnâtiônàlizætiøn     | Row 1 Col 3        |
def');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n\nabc"]],
            ['p_close',[]],
            ['table_open',[3, 2, 6]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Iñtërnâtiônàlizætiøn     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 0 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['tablerow_open',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 1    ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Iñtërnâtiônàlizætiøn     ']],
            ['tablecell_close',[]],
            ['tablecell_open',[1,'left',1]],
            ['cdata',[' Row 1 Col 3        ']],
            ['tablecell_close',[]],
            ['tablerow_close',[]],
            ['table_close',[153]],
            ['p_open',[]],
            ['cdata',['def']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAcronym() {
        $t = ['Iñtërnâtiônàlizætiøn'];
        $this->P->addMode('acronym',new Acronym($t));
        $this->P->parse("Foo Iñtërnâtiônàlizætiøn Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nFoo "]],
            ['acronym',['Iñtërnâtiônàlizætiøn']],
            ['cdata',[" Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwiki() {
        $this->P->addMode('internallink',new InternalLink());
        $this->P->parse("Foo [[wp>Iñtërnâtiônàlizætiøn|Iñtërnâtiônàlizætiøn]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['interwikilink',['wp>Iñtërnâtiônàlizætiøn','Iñtërnâtiônàlizætiøn','wp','Iñtërnâtiônàlizætiøn']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLink() {
        $this->P->addMode('internallink',new InternalLink());
        $this->P->parse("Foo [[x:Iñtërnâtiônàlizætiøn:y:foo_bar:z|Iñtërnâtiônàlizætiøn]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['x:Iñtërnâtiônàlizætiøn:y:foo_bar:z','Iñtërnâtiônàlizætiøn']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

}
