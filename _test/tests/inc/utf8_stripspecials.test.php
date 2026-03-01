<?php

class utf8_stripspecials extends DokuWikiTest
{

    /**
     * @return array
     * @see testGivens
     */
    function provideGivens()
    {
        return [
            ['asciistring', '', '', 'asciistring'],
            ['asciistring', '', '\._\-:', 'asciistring'],
            ['ascii.string', '', '\._\-:', 'asciistring'],
            ['ascii.string', ' ', '\._\-:', 'ascii string'],
            ['2.1.14', ' ', '\._\-:', '2 1 14'],
            ['ascii.string', '', '\._\-:\*', 'asciistring'],
            ['ascii.string', ' ', '\._\-:\*', 'ascii string'],
            ['2.1.14', ' ', '\._\-:\*', '2 1 14'],
            ['string with nbsps', '_', '\*', 'string_with_nbsps'],
            ['αβγδεϝϛζηθικλμνξοπϟϙρστυφχψωϡ', '_', '', 'αβγδεϝϛζηθικλμνξοπϟϙρστυφχψωϡ'], // #3188
        ];
    }

    /**
     * @param string $string
     * @param string $replacement
     * @param string $additional
     * @param string $expected
     * @dataProvider provideGivens
     */
    function testGivens($string, $replacement, $additional, $expected)
    {
        $this->assertEquals($expected, \dokuwiki\Utf8\Clean::stripspecials($string, $replacement, $additional));
    }

}
