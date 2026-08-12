<?php

namespace dokuwiki\test\inc;

/**
 * Regression tests for issue #4718: userlink()/editorinfo() must return plain
 * text (no HTML entities) when called with $textonly = true, even when the
 * wiki is configured to show editors as email addresses.
 */
class UserlinkTextonlyTest extends \DokuWikiTest
{
    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['REMOTE_USER'] = 'testuser';
    }

    /**
     * With showuseras = email and the default mailguard = hex, the text-only
     * variant must return the raw address instead of the obfuscated entities.
     */
    public function testTextonlyEmailReturnsPlainAddress(): void
    {
        global $conf;
        $conf['showuseras'] = 'email';
        $conf['mailguard'] = 'hex';

        $this->assertEquals('arthur@example.com', userlink('testuser', true));
    }

    /**
     * Same contract for showuseras = email_link.
     */
    public function testTextonlyEmailLinkReturnsPlainAddress(): void
    {
        global $conf;
        $conf['showuseras'] = 'email_link';
        $conf['mailguard'] = 'hex';

        $this->assertEquals('arthur@example.com', userlink('testuser', true));
    }

    /**
     * The HTML variant must keep returning obfuscated entities: the fix must
     * not weaken mail obfuscation for non-text output.
     */
    public function testHtmlEmailStillObfuscated(): void
    {
        global $conf;
        $conf['showuseras'] = 'email';
        $conf['mailguard'] = 'hex';

        $html = userlink('testuser', false);
        $this->assertStringContainsString('&#', $html);
        $this->assertStringNotContainsString('arthur@example.com', $html);
    }
}
