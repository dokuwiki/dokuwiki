<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Internallink;

/**
 * Tests for the {@see Internallink} parser mode: `[[target|title]]` dispatch.
 *
 * Covers internal pages, namespaces, section refs, interwiki labels, and the cases where the target
 * dispatches to externallink (URL inside [[ ]]), emaillink (email inside [[ ]]), windowssharelink, or
 * a media payload as the link title.
 *
 * @group parser_links
 */
class InternallinkTest extends ParserTestBase
{
    function testOneChar() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[l]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['l', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNoChar() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNamespaceNoTitle() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[foo:bar]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['foo:bar', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNamespace() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|Test]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['x:1:y:foo_bar:z', 'Test']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSectionRef() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[wiki:syntax#internal|Syntax]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['wiki:syntax#internal', 'Syntax']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeFollows() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[wiki:internal:link|Test]] Bar <code>command [arg1 [arg2 [arg3]]]</code>");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['wiki:internal:link', 'Test']],
            ['cdata', [' Bar <code>command [arg1 [arg2 [arg3]]]</code>']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCodeFollows2() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[wiki:internal:link|[Square brackets in title] Test]] Bar <code>command [arg1 [arg2 [arg3]]]</code>");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['wiki:internal:link', '[Square brackets in title] Test']],
            ['cdata', [' Bar <code>command [arg1 [arg2 [arg3]]]</code>']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTwoLinks() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[foo:bar|one]] and [[bar:foo|two]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['foo:bar', 'one']],
            ['cdata', [' and ']],
            ['internallink', ['bar:foo', 'two']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- dispatch to externallink -----

    function testExternalUrlInside() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[http://www.google.com|Google]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://www.google.com', 'Google']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalUrlWithBracketsInside() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[http://www.google.com?test[]=squarebracketsinurl|Google]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://www.google.com?test[]=squarebracketsinurl', 'Google']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalUrlWithBracketsInsideCodeFollows() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[http://www.google.com?test[]=squarebracketsinurl|Google]] Bar <code>command [arg1 [arg2 [arg3]]]</code>");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://www.google.com?test[]=squarebracketsinurl', 'Google']],
            ['cdata', [' Bar <code>command [arg1 [arg2 [arg3]]]</code>']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFileSchemeInside() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse('Foo [[file://temp/file.txt|Some File]] Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['file://temp/file.txt', 'Some File']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- dispatch to interwikilink -----

    function testInterwikiLink() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[iw>somepage|Some Page]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['interwikilink', ['iw>somepage', 'Some Page', 'iw', 'somepage']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwikiLinkCase() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[IW>somepage|Some Page]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['interwikilink', ['IW>somepage', 'Some Page', 'iw', 'somepage']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwikiPedia() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[wp>Callback_(computer_science)|callbacks]] Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['interwikilink', ['wp>Callback_(computer_science)', 'callbacks', 'wp', 'Callback_(computer_science)']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- media payload as link title -----

    function testMediaImageAsTitle() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|{{img.gif?10x20nocache|Some Image}}]] Bar");

        $image = [
            'type'    => 'internalmedia',
            'src'     => 'img.gif',
            'title'   => 'Some Image',
            'align'   => null,
            'width'   => 10,
            'height'  => 20,
            'cache'   => 'nocache',
            'linking' => 'details',
        ];

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['x:1:y:foo_bar:z', $image]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaNonImageAsTitle() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[x:1:y:foo_bar:z|{{foo.txt?10x20nocache|Some Image}}]] Bar");

        $image = [
            'type'    => 'internalmedia',
            'src'     => 'foo.txt',
            'title'   => 'Some Image',
            'align'   => null,
            'width'   => 10,
            'height'  => 20,
            'cache'   => 'nocache',
            'linking' => 'details',
        ];

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internallink', ['x:1:y:foo_bar:z', $image]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMediaAsEmailLinkTitle() {
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse("Foo [[foo@example.com|{{img.gif?10x20nocache|Some Image}}]] Bar");

        $image = [
            'type'    => 'internalmedia',
            'src'     => 'img.gif',
            'title'   => 'Some Image',
            'align'   => null,
            'width'   => 10,
            'height'  => 20,
            'cache'   => 'nocache',
            'linking' => 'details',
        ];

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['emaillink', ['foo@example.com', $image]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
