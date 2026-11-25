<?php

namespace dokuwiki\test\Utf8;

use dokuwiki\Utf8\Clean;

/**
 * Tests for dokuwiki\Utf8\Clean
 */
class CleanTest extends \DokuWikiTest
{
    /**
     * @return array[] input, replacement, expected output
     */
    public function provideReplaceBadBytes()
    {
        return [
            'valid text' => ["ASCII üñíçødé € 😀\n\t\x00", '', "ASCII üñíçødé € 😀\n\t\x00"],
            'empty string' => ['', '?', ''],
            'stray byte' => ["a\xFFb", '', 'ab'],
            'stray continuation byte' => ["a\x80b", '?', 'a?b'],
            'truncated sequence' => ["a\xE2\x82b", '?', 'a??b'],
            'overlong sequence' => ["a\xC0\xAFb", '?', 'a??b'],
            'surrogate' => ["a\xED\xA0\x80b", '', 'ab'],
            'above U+10FFFF' => ["a\xF4\x90\x80\x80b", '', 'ab'],
            'valid around bad bytes' => ["ü\xFFü\xFF", '', 'üü'],
            'replacement with special chars' => ["a\xFFb", '$0\\1', 'a$0\\1b'],
        ];
    }

    /**
     * @dataProvider provideReplaceBadBytes
     */
    public function testReplaceBadBytes($input, $replace, $expected)
    {
        $this->assertSame($expected, Clean::replaceBadBytes($input, $replace));
    }
}
