<?php

namespace dokuwiki\test;

use dokuwiki\MailUtils;

class MailUtilsTest extends \DokuWikiTest
{
    // region obfuscate() — basic modes

    public function testObfuscateNone(): void
    {
        global $conf;
        $conf['mailguard'] = 'none';
        $this->assertEquals('jon-doe@example.com', MailUtils::obfuscate('jon-doe@example.com'));
    }

    public function testObfuscateHex(): void
    {
        global $conf;
        $conf['mailguard'] = 'hex';
        $this->assertEquals(
            '&#106;&#111;&#110;&#45;&#100;&#111;&#101;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;',
            MailUtils::obfuscate('jon-doe@example.com')
        );
    }

    public function testObfuscateVisible(): void
    {
        global $conf;
        $conf['mailguard'] = 'visible';
        $this->assertEquals('jon [dash] doe [at] example [dot] com', MailUtils::obfuscate('jon-doe@example.com'));
    }

    public function testObfuscateHexQueryPreserved(): void
    {
        global $conf;
        $conf['mailguard'] = 'hex';
        // The query string is preserved verbatim (with HTML escaping only) so
        // mail clients can pre-fill the subject; only the address half is
        // hex-encoded.
        $this->assertEquals(
            '&#117;&#115;&#101;&#114;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;?subject=Привет',
            MailUtils::obfuscate('user@example.com?subject=Привет')
        );
    }

    // endregion
    // region obfuscate() / obfuscateUrl() — bug #1690 (multiple query params)

    public function testNoneWithQuery(): void
    {
        global $conf;
        $conf['mailguard'] = 'none';
        $this->assertEquals(
            'user@example.com?body=Hello.&amp;subject=DOC REQUEST',
            MailUtils::obfuscate('user@example.com?body=Hello.&subject=DOC REQUEST')
        );
        $this->assertEquals(
            'user@example.com?body=Hello.&amp;subject=DOC REQUEST',
            MailUtils::obfuscateUrl('user@example.com?body=Hello.&subject=DOC REQUEST')
        );
    }

    public function testHexWithQuery(): void
    {
        global $conf;
        $conf['mailguard'] = 'hex';
        $expected = '&#117;&#115;&#101;&#114;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;'
            . '&#99;&#111;&#109;?body=Hello.&amp;subject=DOC REQUEST';
        $this->assertEquals(
            $expected,
            MailUtils::obfuscate('user@example.com?body=Hello.&subject=DOC REQUEST')
        );
        $this->assertEquals(
            $expected,
            MailUtils::obfuscateUrl('user@example.com?body=Hello.&subject=DOC REQUEST')
        );
    }

    public function testVisibleWithQuery(): void
    {
        global $conf;
        $conf['mailguard'] = 'visible';
        // Only the address half is touched: dots/dashes inside body/subject
        // values stay intact.
        $this->assertEquals(
            'user [at] example [dot] com?body=Hello.&amp;subject=DOC REQUEST',
            MailUtils::obfuscate('user@example.com?body=Hello.&subject=DOC REQUEST')
        );
        // For the href, the [at]/[dot] address is percent-encoded so the URL
        // is well-formed; the query string is preserved verbatim with only
        // HTML-attribute escaping applied.
        $this->assertEquals(
            'user%20%5Bat%5D%20example%20%5Bdot%5D%20com?body=Hello.&amp;subject=DOC REQUEST',
            MailUtils::obfuscateUrl('user@example.com?body=Hello.&subject=DOC REQUEST')
        );
    }

    /**
     * Regression: never emit a double-escaped &amp;amp; in any mode.
     */
    public function testNoDoubleEscape(): void
    {
        global $conf;
        $input = 'user@example.com?a=1&b=2&c=3';
        foreach (['none', 'hex', 'visible'] as $mode) {
            $conf['mailguard'] = $mode;
            $this->assertStringNotContainsString('&amp;amp;', MailUtils::obfuscate($input), "obfuscate/$mode");
            $this->assertStringNotContainsString(
                '&amp;amp;',
                MailUtils::obfuscateUrl($input),
                "obfuscateUrl/$mode"
            );
        }
    }

    // endregion
    // region obfuscateUrl() — URL-shape coverage without query

    public function testObfuscateUrlNoneNoQuery(): void
    {
        global $conf;
        $conf['mailguard'] = 'none';
        $this->assertEquals('jon-doe@example.com', MailUtils::obfuscateUrl('jon-doe@example.com'));
    }

    public function testObfuscateUrlHexNoQuery(): void
    {
        global $conf;
        $conf['mailguard'] = 'hex';
        $this->assertEquals(
            '&#106;&#111;&#110;&#45;&#100;&#111;&#101;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;',
            MailUtils::obfuscateUrl('jon-doe@example.com')
        );
    }

    public function testObfuscateUrlVisibleNoQuery(): void
    {
        global $conf;
        $conf['mailguard'] = 'visible';
        // Visible mode percent-encodes the [at]/[dot] address so the mailto
        // URL stays well-formed.
        $this->assertEquals(
            'jon%20%5Bdash%5D%20doe%20%5Bat%5D%20example%20%5Bdot%5D%20com',
            MailUtils::obfuscateUrl('jon-doe@example.com')
        );
    }

    /**
     * HTML-special characters in the address half must never break out of the
     * attribute context, regardless of mode.
     */
    public function testObfuscateEscapesHtmlSpecials(): void
    {
        global $conf;
        // Constructed so the address half contains a quote-like char that
        // htmlspecialchars must neutralise; chosen to still pass the
        // [at]/[dot] substitution path.
        $input = "a'b@example.com";
        foreach (['none', 'visible'] as $mode) {
            $conf['mailguard'] = $mode;
            $this->assertStringNotContainsString("'", MailUtils::obfuscate($input), "obfuscate/$mode");
            $this->assertStringNotContainsString("'", MailUtils::obfuscateUrl($input), "obfuscateUrl/$mode");
        }
    }

    // endregion
    // region isValid()

    public function provideAddresses(): array
    {
        return [
            // our own tests
            ['bugs@php.net', true],
            ['~someone@somewhere.com', true],
            ['no+body.here@somewhere.com.au', true],
            ['username+tag@domain.com', true], // FS#1447
            ["rfc2822+allthesechars_#*!'`/-={}are.legal@somewhere.com.au", true],
            ['_foo@test.com', true], // FS#1049
            ['bugs@php.net1', true], // new ICAN rulez seem to allow this
            ['.bugs@php.net1', false],
            ['bu..gs@php.net', false],
            ['bugs@php..net', false],
            ['bugs@.php.net', false],
            ['bugs@php.net.', false],
            ['bu(g)s@php.net1', false],
            ['bu[g]s@php.net1', false],
            ['somebody@somewhere.museum', true],
            ['somebody@somewhere.travel', true],
            ['root@[2010:fb:fdac::311:2101]', true],
            ['test@example', true], // we allow local addresses

            // tests from http://code.google.com/p/php-email-address-validation/ below
            ['test@example.com', true],
            ['TEST@example.com', true],
            ['1234567890@example.com', true],
            ['test+test@example.com', true],
            ['test-test@example.com', true],
            ['t*est@example.com', true],
            ['+1~1+@example.com', true],
            ['{_test_}@example.com', true],
            ['"[[ test ]]"@example.com', true],
            ['test.test@example.com', true],
            ['test."test"@example.com', true],
            ['"test@test"@example.com', true],
            ['test@123.123.123.123', true],
            ['test@[123.123.123.123]', true],
            ['test@example.example.com', true],
            ['test@example.example.example.com', true],

            ['test.example.com', false],
            ['test.@example.com', false],
            ['test..test@example.com', false],
            ['.test@example.com', false],
            ['test@test@example.com', false],
            ['test@@example.com', false],
            ['-- test --@example.com', false], // No spaces allowed in local part
            ['[test]@example.com', false], // Square brackets only allowed within quotes
            ['"test\test"@example.com', false], // Quotes cannot contain backslash
            ['"test"test"@example.com', false], // Quotes cannot be nested
            ['()[]\;:,<>@example.com', false], // Disallowed Characters
            ['test@.', false],
            ['test@example.', false],
            ['test@.org', false],
            // 64 characters is maximum length for local part. This is 65.
            ['12345678901234567890123456789012345678901234567890123456789012345@example.com', false],
            // 255 characters is maximum length for domain. This is 256.
            ['test@123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012.com', false],
            ['test@[123.123.123.123', false],
            ['test@123.123.123.123]', false],
        ];
    }

    /**
     * @dataProvider provideAddresses
     */
    public function testIsValid(string $input, bool $valid): void
    {
        $this->assertSame($valid, MailUtils::isValid($input));
    }

    // endregion
    // region quotedPrintableEncode()

    public function testQuotedPrintableSimple(): void
    {
        $this->assertEquals('hello', MailUtils::quotedPrintableEncode('hello'));
    }

    public function testQuotedPrintableSpaceEnd(): void
    {
        $this->assertEquals("hello=20\r\nhello", MailUtils::quotedPrintableEncode("hello \nhello"));
    }

    public function testQuotedPrintableGermanUtf8(): void
    {
        $this->assertEquals(
            'hello =C3=BCberl=C3=A4nge',
            MailUtils::quotedPrintableEncode('hello überlänge')
        );
    }

    public function testQuotedPrintableWrap(): void
    {
        $in  = '123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789';
        $out = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 1234=\r\n56789 123456789";
        $this->assertEquals($out, MailUtils::quotedPrintableEncode($in, 74));
    }

    public function testQuotedPrintableNoWrap(): void
    {
        $line = '123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789';
        $this->assertEquals($line, MailUtils::quotedPrintableEncode($line, 0));
    }

    public function testQuotedPrintableRussianUtf8(): void
    {
        $in  = 'Ваш пароль для системы Доку Вики';
        $out = '=D0=92=D0=B0=D1=88 =D0=BF=D0=B0=D1=80=D0=BE=D0=BB=D1=8C '
            . '=D0=B4=D0=BB=D1=8F =D1=81=D0=B8=D1=81=D1=82=D0=B5=D0=BC=D1=8B '
            . '=D0=94=D0=BE=D0=BA=D1=83 =D0=92=D0=B8=D0=BA=D0=B8';
        $this->assertEquals($out, MailUtils::quotedPrintableEncode($in, 0));
    }

    // endregion
    // region PREG_PATTERN_VALID_EMAIL

    public function testPatternMatchesValid(): void
    {
        $this->assertSame(
            1,
            preg_match('<' . MailUtils::PREG_PATTERN_VALID_EMAIL . '>', 'user@example.com')
        );
    }

    public function testPatternRejectsInvalid(): void
    {
        // The pattern is the parser's syntax detector, not a strict validator;
        // it must at least reject inputs with no '@'.
        $this->assertSame(
            0,
            preg_match('<^' . MailUtils::PREG_PATTERN_VALID_EMAIL . '$>', 'not-an-email')
        );
    }

    // endregion
}
