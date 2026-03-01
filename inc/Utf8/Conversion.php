<?php

namespace dokuwiki\Utf8;

/**
 * Methods to convert from and to UTF-8 strings
 */
class Conversion
{
    /**
     * Encodes UTF-8 characters to HTML entities
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author <vpribish at shopping dot com>
     * @link   http://php.net/manual/en/function.utf8-decode.php
     *
     * @param string $str
     * @param bool $all Encode non-utf8 char to HTML as well
     * @return string
     */
    public static function toHtml($str, $all = false)
    {
        $ret = '';
        foreach (Unicode::fromUtf8($str) as $cp) {
            if ($cp < 0x80 && !$all) {
                $ret .= chr($cp);
            } elseif ($cp < 0x100) {
                $ret .= "&#$cp;";
            } else {
                $ret .= '&#x' . dechex($cp) . ';';
            }
        }
        return $ret;
    }

    /**
     * Decodes HTML entities to UTF-8 characters
     *
     * Convert any &#..; entity to a codepoint,
     * The entities flag defaults to only decoding numeric entities.
     * Pass HTML_ENTITIES and named entities, including &amp; &lt; etc.
     * are handled as well. Avoids the problem that would occur if you
     * had to decode "&amp;#38;&#38;amp;#38;"
     *
     * unhtmlspecialchars(\dokuwiki\Utf8\Conversion::fromHtml($s)) -> "&#38;&#38;"
     * \dokuwiki\Utf8\Conversion::fromHtml(unhtmlspecialchars($s)) -> "&&amp#38;"
     * what it should be                   -> "&#38;&amp#38;"
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @param  string $str UTF-8 encoded string
     * @param  boolean $entities decode name entities in addtition to numeric ones
     * @return string  UTF-8 encoded string with numeric (and named) entities replaced.
     */
    public static function fromHtml($str, $entities = false)
    {
        if (!$entities) {
            return preg_replace_callback(
                '/(&#([Xx])?([0-9A-Za-z]+);)/m',
                [self::class, 'decodeNumericEntity'],
                $str
            );
        }

        return preg_replace_callback(
            '/&(#)?([Xx])?([0-9A-Za-z]+);/m',
            [self::class, 'decodeAnyEntity'],
            $str
        );
    }

    /**
     * Decodes any HTML entity to it's correct UTF-8 char equivalent
     *
     * @param string $ent An entity
     * @return string
     */
    protected static function decodeAnyEntity($ent)
    {
        // create the named entity lookup table
        static $table = null;
        if ($table === null) {
            $table = get_html_translation_table(HTML_ENTITIES);
            $table = array_flip($table);
            $table = array_map(
                static fn($c) => Unicode::toUtf8([ord($c)]),
                $table
            );
        }

        if ($ent[1] === '#') {
            return self::decodeNumericEntity($ent);
        }

        if (array_key_exists($ent[0], $table)) {
            return $table[$ent[0]];
        }

        return $ent[0];
    }

    /**
     * Decodes numeric HTML entities to their correct UTF-8 characters
     *
     * @param $ent string A numeric entity
     * @return string|false
     */
    protected static function decodeNumericEntity($ent)
    {
        switch ($ent[2]) {
            case 'X':
            case 'x':
                $cp = hexdec($ent[3]);
                break;
            default:
                $cp = (int) $ent[3];
                break;
        }
        return Unicode::toUtf8([$cp]);
    }

    /**
     * UTF-8 to UTF-16BE conversion.
     *
     * Maybe really UCS-2 without mb_string due to utf8_to_unicode limits
     *
     * @param string $str
     * @param bool $bom
     * @return string
     */
    public static function toUtf16be($str, $bom = false)
    {
        $out = $bom ? "\xFE\xFF" : '';
        if (UTF8_MBSTRING) {
            return $out . mb_convert_encoding($str, 'UTF-16BE', 'UTF-8');
        }

        $uni = Unicode::fromUtf8($str);
        foreach ($uni as $cp) {
            $out .= pack('n', $cp);
        }
        return $out;
    }

    /**
     * UTF-8 to UTF-16BE conversion.
     *
     * Maybe really UCS-2 without mb_string due to utf8_to_unicode limits
     *
     * @param string $str
     * @return false|string
     */
    public static function fromUtf16be($str)
    {
        $uni = unpack('n*', $str);
        return Unicode::toUtf8($uni);
    }

    /**
     * Converts a string from ISO-8859-1 to UTF-8
     *
     * This is a replacement for the deprecated utf8_encode function.
     *
     * @param $string
     * @return string
     * @author <p@tchwork.com> Nicolas Grekas - pure PHP implementation
     * @link https://github.com/tchwork/utf8/blob/master/src/Patchwork/PHP/Shim/Xml.php
     */
    public static function fromLatin1($string)
    {
        if (UTF8_MBSTRING) {
            return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        if (function_exists('iconv')) {
            return iconv('ISO-8859-1', 'UTF-8', $string);
        }
        if (class_exists('UConverter')) {
            return \UConverter::transcode($string, 'UTF8', 'ISO-8859-1');
        }
        if (function_exists('utf8_encode')) {
            // deprecated
            return utf8_encode($string);
        }

        // fallback to pure PHP
        $string .= $string;
        $len = strlen($string);
        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $string[$i] < "\x80":
                    $string[$j] = $string[$i];
                    break;
                case $string[$i] < "\xC0":
                    $string[$j] = "\xC2";
                    $string[++$j] = $string[$i];
                    break;
                default:
                    $string[$j] = "\xC3";
                    $string[++$j] = chr(ord($string[$i]) - 64);
                    break;
            }
        }
        return substr($string, 0, $j);
    }
}
