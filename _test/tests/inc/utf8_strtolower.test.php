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
            [
                'Α Β Γ Δ Ε Ϝ Ϛ Ζ Η Θ Ι Κ Λ Μ Ν Ξ Ο Π Ϟ ϙ Ρ Σ Τ Υ Φ Χ Ψ Ω Ϡ',
                'α β γ δ ε ϝ ϛ ζ η θ ι κ λ μ ν ξ ο π ϟ ϙ ρ σ τ υ φ χ ψ ω ϡ'
            ], // #3188
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
