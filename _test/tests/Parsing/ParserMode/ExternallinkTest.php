<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\Externallink;
use dokuwiki\Parsing\ParserMode\Internallink;

/**
 * Tests for the {@see Externallink} parser mode.
 *
 * Covers the classic DokuWiki autolink behavior (bare URLs, www./ftp. shortcuts, IPv4/IPv6,
 * scheme allow-listing), the Markdown angle-bracket autolink form (CommonMark §6.5), and the
 * GFM autolink extension trim step (paren balancing, trailing entity-ref decoding).
 *
 * @group parser_links
 */
class ExternallinkTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $conf['syntax'] = 'md';
        ModeRegistry::reset();
    }

    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    // ----- basic bare-URL autolink -----

    function testSimple() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo http://www.google.com Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://www.google.com', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCase() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo HTTP://WWW.GOOGLE.COM Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['HTTP://WWW.GOOGLE.COM', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testIPv4() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo http://123.123.3.21/foo Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://123.123.3.21/foo', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testIPv6() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo http://[3ffe:2a00:100:7031::1]/foo Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://[3ffe:2a00:100:7031::1]/foo', null]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMulti() {
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
        $titles = [false, null, 'foo bar'];
        foreach ($links as $link) {
            foreach ($titles as $title) {
                if ($title === false) {
                    $source = $link;
                    $name = null;
                } elseif ($title === null) {
                    $source = "[[$link]]";
                    $name = null;
                } else {
                    $source = "[[$link|$title]]";
                    $name = $title;
                }
                $this->setup();
                $this->P->addMode('internallink', new Internallink());
                $this->P->addMode('externallink', new Externallink());
                $this->P->parse("Foo $source Bar");
                $calls = [
                    ['document_start', []],
                    ['p_open', []],
                    ['cdata', ["\n" . 'Foo ']],
                    ['externallink', [$link, $name]],
                    ['cdata', [' Bar']],
                    ['p_close', []],
                    ['document_end', []],
                ];
                $this->assertCalls($calls, $this->H->calls, $source);
                $this->teardown();
            }
        }

        $this->setup();
    }

    function testJavascriptScheme() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo javascript:alert('XSS'); Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo javascript:alert('XSS'); Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- www. / ftp. shortcuts -----

    function testWWWLink() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo www.google.com Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://www.google.com', 'www.google.com']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWWWLinkStartOfLine() {
        // Regression test for issue #2399
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['externallink', ['http://www.google.com', 'www.google.com']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $instructions = p_get_instructions("www.google.com Bar");
        $this->assertCalls($calls, $instructions);
    }

    function testWWWLinkInRoundBrackets() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo (www.google.com) Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo (']],
            ['externallink', ['http://www.google.com', 'www.google.com']],
            ['cdata', [') Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWWWLinkInPath() {
        $this->P->addMode('externallink', new Externallink());
        // See issue #936. Should NOT generate a link!
        $this->P->parse("Foo /home/subdir/www/www.something.de/somedir/ Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo /home/subdir/www/www.something.de/somedir/ Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWWWLinkFollowingPath() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo /home/subdir/www/ www.something.de/somedir/ Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo /home/subdir/www/ ']],
            ['externallink', ['http://www.something.de/somedir/', 'www.something.de/somedir/']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFTPLink() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo ftp.sunsite.com Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['ftp://ftp.sunsite.com', 'ftp.sunsite.com']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFTPLinkInPath() {
        $this->P->addMode('externallink', new Externallink());
        // See issue #936. Should NOT generate a link!
        $this->P->parse("Foo /home/subdir/www/ftp.something.de/somedir/ Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo /home/subdir/www/ftp.something.de/somedir/ Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFTPLinkFollowingPath() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo /home/subdir/www/ ftp.something.de/somedir/ Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo /home/subdir/www/ ']],
            ['externallink', ['ftp://ftp.something.de/somedir/', 'ftp.something.de/somedir/']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- Markdown angle-bracket autolinks (§6.5) -----

    function testAngleBracketAutolink() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo <http://www.google.com> Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externallink', ['http://www.google.com', 'http://www.google.com']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAngleBracketDisqualifiedByInternalWhitespace() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo <http://www.google.com bim> Bar");
        // Internal whitespace disqualifies the autolink. The whole envelope is consumed as cdata so the
        // bare-URL detector cannot pick up http://www.google.com inside and leave dangling brackets.
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo <http://www.google.com bim> Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAngleBracketDisqualifiedByLeadingWhitespace() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse("Foo < http://www.google.com > Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo < http://www.google.com > Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAngleBracketUnregisteredScheme() {
        $this->P->addMode('externallink', new Externallink());
        // mailto is not in the default conf/scheme.conf allow-list, so no per-scheme angle pattern is built
        // for it. The brackets fall through to cdata, matching DokuWiki's bare-URL scheme policy.
        $this->P->parse("Foo <mailto:foo@example.com> Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo <mailto:foo@example.com> Bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAngleBracketInactiveInDwMode() {
        global $conf;
        $conf['syntax'] = 'dw';
        $this->P->addMode('externallink', new Externallink());
        // In DW-only syntax, angle-bracket processing is intentionally not active. The bare-URL pattern still
        // picks up the URL inside and the angle brackets fall through as literal text — matches the pre-merge
        // behavior of DokuWiki's Externallink mode.
        $this->P->parse("Foo <http://www.google.com> Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo <']],
            ['externallink', ['http://www.google.com', null]],
            ['cdata', ['> Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- GFM autolink extension: paren balancing -----

    function testBalancedParensInUrl() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See www.example.com/path(foo) end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://www.example.com/path(foo)', 'www.example.com/path(foo)']],
            ['cdata', [' end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTrailingUnbalancedParenExcluded() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See (www.example.com/path(foo)) end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee ("]],
            ['externallink', ['http://www.example.com/path(foo)', 'www.example.com/path(foo)']],
            ['cdata', [') end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleTrailingParensTrimmedUntilBalanced() {
        $this->P->addMode('externallink', new Externallink());
        // Inner `(foo)` is balanced and stays inside the URL; the two unbalanced trailing `)` are peeled off.
        $this->P->parse('See www.example.com/path(foo))) end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://www.example.com/path(foo)', 'www.example.com/path(foo)']],
            ['cdata', [')) end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testParenInsideUrlNoTrailing() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See www.example.com/search?q=(business))+ok end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', [
                'http://www.example.com/search?q=(business))+ok',
                'www.example.com/search?q=(business))+ok'
            ]],
            ['cdata', [' end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- GFM autolink extension: trailing entity references -----

    function testTrailingValidEntityDecodedToUnicode() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See http://example.com/&copy; end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://example.com/', null]],
            ['cdata', ['© end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTrailingUnknownEntityRoundTripsLiterally() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See http://example.com/&hl; end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://example.com/', null]],
            ['cdata', ['&hl; end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTrailingNumericEntityDecoded() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See http://example.com/&#65; end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://example.com/', null]],
            ['cdata', ['A end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNonTrailingEntityStaysInsideUrl() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See http://example.com/&copy;more end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://example.com/&copy;more', null]],
            ['cdata', [' end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMixtureParenThenEntityPeelsBoth() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See (http://example.com/path)&copy; end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee ("]],
            ['externallink', ['http://example.com/path', null]],
            ['cdata', [')© end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMixtureMultipleEntitiesAndParens() {
        $this->P->addMode('externallink', new Externallink());
        $this->P->parse('See http://example.com/)&copy;)&hl; end');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nSee "]],
            ['externallink', ['http://example.com/', null]],
            ['cdata', [')©)&hl; end']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }
}
