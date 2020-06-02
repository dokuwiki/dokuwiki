<?php

class utf8_strtolower_test extends DokuWikiTest
{

    /**
     * @see testGivens
     * @return array
     */
    public function provideGivens()
    {
        return [
            ['Αρχιτεκτονική Μελέτη', 'αρχιτεκτονική μελέτη'], // FS#2173
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'],
            ['players:Bruce', 'players:bruce'],
            ['players:GERALD', 'players:gerald'],
        ];
    }

    /**
     * @dataProvider provideGivens
     * @param string $input
     * @param string $expected
     */
    public function testGivens($input, $expected)
    {
        $this->assertEquals($expected, \dokuwiki\Utf8\PhpString::strtolower($input));
        // just make sure our data was correct
        $this->assertEquals($expected, mb_strtolower($input, 'utf-8'), 'mbstring check');
    }
}
