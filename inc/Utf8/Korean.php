<?php

namespace dokuwiki\Utf8;

/**
 * Korean-specific conversions and operations.
 */
class Korean
{
    /**
     * Korean codepoint.
     */
    public const START = 0xAC00;
    public const END = 0xD7AF;
    public const REGEX = '/[\x{AC00}-\x{D7AF}]/u';

    /**
     * Define Korean romanization table.
     *
     * It can't be 1:1 since first and third element have different pronounciation.
     */
    protected const PART1_TABLE = array(
        'g', 'kk', 'n', 'd', 'tt', 'r', 'm', 'b', 'pp', 's',
        'ss', '', 'j', 'jj', 'ch', 'k', 't', 'p', 'h'
    );
    protected const PART2_TABLE = array(
        'a', 'ae', 'ya', 'yae', 'eo', 'e', 'yeo', 'ye', 'o', 'wa',
        'wae', 'oe', 'yo', 'u', 'wo', 'we', 'wi', 'yu', 'eu', 'ui',
        'i'
    );
    protected const PART3_TABLE = array(
        '', 'k', 'k', 'k', 'n', 'n', 'n', 't', 'l', 'k',
        'm', 'p', 't', 't', 'p', 'l', 'm', 'p', 'p', 't',
        't', 'ng', 't', 't', 'k', 't', 'p', ''
    );


    /**
     * Return romanization of single character.
     */
    public static function romanizeKoreanCharacter($char)
    {
        $code = mb_ord($char, 'UTF-8') - self::START;
        $result = [];

        $part1_index = intdiv($code, 21 * 28);
        $part2_index = intdiv($code % (21 * 28), 28);
        $part3_index = $code % 28;

        $result[] = Korean::PART1_TABLE[$part1_index];
        $result[] = Korean::PART2_TABLE[$part2_index];
        $result[] = Korean::PART3_TABLE[$part3_index];

        return implode($result);
    }
}
