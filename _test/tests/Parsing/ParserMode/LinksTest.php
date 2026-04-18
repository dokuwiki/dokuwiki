<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Camelcaselink;
use dokuwiki\Parsing\ParserMode\Emaillink;
use dokuwiki\Parsing\ParserMode\Externallink;
use dokuwiki\Parsing\ParserMode\Filelink;
use dokuwiki\Parsing\ParserMode\Internallink;
use dokuwiki\Parsing\ParserMode\Media;
use dokuwiki\Parsing\ParserMode\Windowssharelink;

/**
 * Tests for the implementation of link syntax
 *
 * @group parser_links
*/
class LinksTest extends ParserTestBase
{

    function testExternalLinkSimple() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo http://www.google.com Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://www.google.com', null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalLinkCase() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo HTTP://WWW.GOOGLE.COM Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['HTTP://WWW.GOOGLE.COM', null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalIPv4() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo http://123.123.3.21/foo Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://123.123.3.21/foo', null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalIPv6() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo http://[3ffe:2a00:100:7031::1]/foo Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://[3ffe:2a00:100:7031::1]/foo', null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalMulti(){
        $this->teardown();

        $links = [
            'http://www.google.com',
            'HTTP://WWW.GOOGLE.COM',
            'http://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80/index.html',
            'http://[1080:0:0:0:8:800:200C:417A]/index.html',
            'http://[3ffe:2a00:100:7031::1]',
            'http://[1080::8:800:200C:417A]/foo',
            'http://[::192.9.5.5]/ipng',
            'http://[::FFFF:129.144.52.38]:80/index.html',
            'http://[2010:836B:4179::836B:4179]',
        ];
        $titles = [false,null,'foo bar'];
        foreach($links as $link){
            foreach($titles as $title){
                if($title === false){
                    $source = $link;
                    $name   = null;
                }elseif($title === null){
                    $source = "[[$link]]";
                    $name   = null;
                }else{
                    $source = "[[$link|$title]]";
                    $name   = $title;
                }
                $this->setup();
                $this->P->addMode('internallink',new Internallink());
                $this->P->addMode('externallink',new Externallink());
                $this->P->parse("Foo $source Bar");
                $calls = [
                    ['document_start',[]],
                    ['p_open',[]],
                    ['cdata',["\n".'Foo ']],
                    ['externallink',[$link, $name]],
                    ['cdata',[' Bar']],
                    ['p_close',[]],
                    ['document_end',[]],
                ];
                $this->assertCalls($calls, $this->H->calls, $source);
                $this->teardown();
            }
        }

        $this->setup();
    }

    function testExternalLinkJavascript() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo javascript:alert('XSS'); Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\nFoo javascript:alert('XSS'); Bar"]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalWWWLink() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo www.google.com Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://www.google.com', 'www.google.com']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalWWWLinkStartOfLine() {
        // Regression test for issue #2399
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['externallink',['http://www.google.com', 'www.google.com']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $instructions = p_get_instructions("www.google.com Bar");
        $this->assertCalls($calls, $instructions);
    }

    function testExternalWWWLinkInRoundBrackets() {
        $this->P->addMode('externallink',new ExternalLink());
        $this->P->parse("Foo (www.google.com) Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo (']],
            ['externallink',['http://www.google.com', 'www.google.com']],
            ['cdata',[') Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalWWWLinkInPath() {
        $this->P->addMode('externallink',new Externallink());
        // See issue #936. Should NOT generate a link!
        $this->P->parse("Foo /home/subdir/www/www.something.de/somedir/ Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo /home/subdir/www/www.something.de/somedir/ Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalWWWLinkFollowingPath() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo /home/subdir/www/ www.something.de/somedir/ Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo /home/subdir/www/ ']],
            ['externallink',['http://www.something.de/somedir/', 'www.something.de/somedir/']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalFTPLink() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo ftp.sunsite.com Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['ftp://ftp.sunsite.com', 'ftp.sunsite.com']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalFTPLinkInPath() {
        $this->P->addMode('externallink',new Externallink());
        // See issue #936. Should NOT generate a link!
        $this->P->parse("Foo /home/subdir/www/ftp.something.de/somedir/ Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo /home/subdir/www/ftp.something.de/somedir/ Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalFTPLinkFollowingPath() {
        $this->P->addMode('externallink',new Externallink());
        $this->P->parse("Foo /home/subdir/www/ ftp.something.de/somedir/ Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo /home/subdir/www/ ']],
            ['externallink',['ftp://ftp.something.de/somedir/', 'ftp.something.de/somedir/']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmail() {
        $this->P->addMode('emaillink',new Emaillink());
        $this->P->parse("Foo <bugs@php.net> Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['emaillink',['bugs@php.net', null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmailRFC2822() {
        $this->P->addMode('emaillink',new Emaillink());
        $this->P->parse("Foo <~fix+bug's.for/ev{e}r@php.net> Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['emaillink',["~fix+bug's.for/ev{e}r@php.net", null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmailCase() {
        $this->P->addMode('emaillink',new Emaillink());
        $this->P->parse("Foo <bugs@pHp.net> Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['emaillink',['bugs@pHp.net', null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }


    function testInternalLinkOneChar() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[l]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['l',null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkNoChar() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['',null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkNamespaceNoTitle() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[foo:bar]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['foo:bar',null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkNamespace() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|Test]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['x:1:y:foo_bar:z','Test']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkSectionRef() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[wiki:syntax#internal|Syntax]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['wiki:syntax#internal','Syntax']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkCodeFollows() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[wiki:internal:link|Test]] Bar <code>command [arg1 [arg2 [arg3]]]</code>");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['wiki:internal:link','Test']],
            ['cdata',[' Bar <code>command [arg1 [arg2 [arg3]]]</code>']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkCodeFollows2() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[wiki:internal:link|[Square brackets in title] Test]] Bar <code>command [arg1 [arg2 [arg3]]]</code>");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['wiki:internal:link','[Square brackets in title] Test']],
            ['cdata',[' Bar <code>command [arg1 [arg2 [arg3]]]</code>']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalInInternalLink() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[http://www.google.com|Google]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://www.google.com','Google']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalInInternalLink2() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[http://www.google.com?test[]=squarebracketsinurl|Google]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://www.google.com?test[]=squarebracketsinurl','Google']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalInInternalLink2CodeFollows() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[http://www.google.com?test[]=squarebracketsinurl|Google]] Bar <code>command [arg1 [arg2 [arg3]]]</code>");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['http://www.google.com?test[]=squarebracketsinurl','Google']],
            ['cdata',[' Bar <code>command [arg1 [arg2 [arg3]]]</code>']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTwoInternalLinks() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[foo:bar|one]] and [[bar:foo|two]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['foo:bar','one']],
            ['cdata',[' and ']],
            ['internallink',['bar:foo','two']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }


    function testInterwikiLink() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[iw>somepage|Some Page]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['interwikilink',['iw>somepage','Some Page','iw','somepage']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwikiLinkCase() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[IW>somepage|Some Page]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['interwikilink',['IW>somepage','Some Page','iw','somepage']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwikiPedia() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[wp>Callback_(computer_science)|callbacks]] Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['interwikilink',['wp>Callback_(computer_science)','callbacks','wp','Callback_(computer_science)']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCamelCase() {
        $this->P->addMode('camelcaselink',new Camelcaselink());
        $this->P->parse("Foo FooBar Bar");
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['camelcaselink',['FooBar']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFileLink() {
        $this->P->addMode('filelink',new FileLink());
        $this->P->parse('Foo file://temp/file.txt Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['filelink',['file://temp/file.txt ',null]],
            ['cdata',['Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFileLinkInternal() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse('Foo [[file://temp/file.txt|Some File]] Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externallink',['file://temp/file.txt','Some File']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWindowsShareLink() {
        $this->P->addMode('windowssharelink',new Windowssharelink());
        $this->P->parse('Foo \\\server\share Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['windowssharelink',['\\\server\share',null]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWindowsShareLinkHyphen() {
        $this->P->addMode('windowssharelink',new Windowssharelink());
        $this->P->parse('Foo \\\server\share-hyphen Bar');
        $calls = [
        ['document_start',[]],
        ['p_open',[]],
        ['cdata',["\n".'Foo ']],
        ['windowssharelink',['\\\server\share-hyphen',null]],
        ['cdata',[' Bar']],
        ['p_close',[]],
        ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWindowsShareLinkInternal() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse('Foo [[\\\server\share|My Documents]] Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['windowssharelink',['\\\server\share','My Documents']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternal() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{img.gif}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif',null,null,null,null,'cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternalLinkOnly() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{img.gif?linkonly}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif',null,null,null,null,'cache','linkonly']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaNotImage() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{foo.txt?10x10|Some File}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['foo.txt','Some File',null,10,10,'cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternalLAlign() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{img.gif }} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif',null,'left',null,null,'cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternalRAlign() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{ img.gif}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif',null,'right',null,null,'cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternalCenter() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{ img.gif }} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif',null,'center',null,null,'cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternalParams() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{img.gif?50x100nocache}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif',null,null,'50','100','nocache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInternalTitle() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{img.gif?50x100|Some Image}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',['img.gif','Some Image',null,'50','100','cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaExternal() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externalmedia',['http://www.google.com/img.gif',null,null,null,null,'cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaExternalParams() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif?50x100nocache}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externalmedia',['http://www.google.com/img.gif',null,null,'50','100','nocache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaExternalTitle() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif?50x100|Some Image}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['externalmedia',
            ['http://www.google.com/img.gif','Some Image',null,'50','100','cache','details']],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInInternalLink() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|{{img.gif?10x20nocache|Some Image}}]] Bar");

        $image = [
            'type'=>'internalmedia',
            'src'=>'img.gif',
            'title'=>'Some Image',
            'align'=>null,
            'width'=>10,
            'height'=>20,
            'cache'=>'nocache',
            'linking'=>'details',
        ];

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['x:1:y:foo_bar:z',$image]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaNoImageInInternalLink() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|{{foo.txt?10x20nocache|Some Image}}]] Bar");

        $image = [
            'type'=>'internalmedia',
            'src'=>'foo.txt',
            'title'=>'Some Image',
            'align'=>null,
            'width'=>10,
            'height'=>20,
            'cache'=>'nocache',
            'linking'=>'details',
        ];

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internallink',['x:1:y:foo_bar:z',$image]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaInEmailLink() {
        $this->P->addMode('internallink',new Internallink());
        $this->P->parse("Foo [[foo@example.com|{{img.gif?10x20nocache|Some Image}}]] Bar");

        $image = [
            'type'=>'internalmedia',
            'src'=>'img.gif',
            'title'=>'Some Image',
            'align'=>null,
            'width'=>10,
            'height'=>20,
            'cache'=>'nocache',
            'linking'=>'details',
        ];

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['emaillink',['foo@example.com',$image]],
            ['cdata',[' Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNestedMedia() {
        $this->P->addMode('media',new Media());
        $this->P->parse('Foo {{img.gif|{{foo.gif|{{bar.gif|Bar}}}}}} Bar');
        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['cdata',["\n".'Foo ']],
            ['internalmedia',
            ['img.gif','{{foo.gif|{{bar.gif|Bar',null,null,null,'cache','details']],
            ['cdata',['}}}} Bar']],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

}
