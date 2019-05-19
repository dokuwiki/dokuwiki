<?php
/**
 * UTF8 helper functions
 *
 * @license    LGPL 2.1 (http://www.gnu.org/copyleft/lesser.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * check for mb_string support
 */
if (!defined('UTF8_MBSTRING')) {
    if (function_exists('mb_substr') && !defined('UTF8_NOMBSTRING')) {
        define('UTF8_MBSTRING', 1);
    } else {
        define('UTF8_MBSTRING', 0);
    }
}

/**
 * Check if PREG was compiled with UTF-8 support
 *
 * Without this many of the functions below will not work, so this is a minimal requirement
 */
if (!defined('UTF8_PREGSUPPORT')) {
    define('UTF8_PREGSUPPORT', (bool)@preg_match('/^.$/u', 'ñ'));
}

/**
 * Check if PREG was compiled with Unicode Property support
 *
 * This is not required for the functions below, but might be needed in a UTF-8 aware application
 */
if (!defined('UTF8_PROPERTYSUPPORT')) {
    define('UTF8_PROPERTYSUPPORT', (bool)@preg_match('/^\pL$/u', 'ñ'));
}


if (UTF8_MBSTRING) {
    mb_internal_encoding('UTF-8');
}


if (!function_exists('utf8_isASCII')) {
    function utf8_isASCII($str)
    {
        return \dokuwiki\Utf8\Clean::isASCII($str);
    }
}


if (!function_exists('utf8_strip')) {
    function utf8_strip($str)
    {
        return \dokuwiki\Utf8\Clean::strip($str);
    }
}

if (!function_exists('utf8_check')) {
    function utf8_check($str)
    {
        return \dokuwiki\Utf8\Clean::isUtf8($str);
    }
}

if (!function_exists('utf8_basename')) {
    function utf8_basename($path, $suffix = '')
    {
        return \dokuwiki\Utf8\PhpString::basename($path, $suffix);
    }
}

if (!function_exists('utf8_strlen')) {
    function utf8_strlen($str)
    {
        return \dokuwiki\Utf8\PhpString::strlen($str);
    }
}

if (!function_exists('utf8_substr')) {
    function utf8_substr($str, $offset, $length = null)
    {
        return \dokuwiki\Utf8\PhpString::substr($str, $offset, $length);
    }
}

if (!function_exists('utf8_substr_replace')) {
    function utf8_substr_replace($string, $replacement, $start, $length = 0)
    {
        return \dokuwiki\Utf8\PhpString::substr_replace($string, $replacement, $start, $length);
    }
}

if (!function_exists('utf8_ltrim')) {
    function utf8_ltrim($str, $charlist = '')
    {
        return \dokuwiki\Utf8\PhpString::ltrim($str, $charlist);
    }
}

if (!function_exists('utf8_rtrim')) {
    function utf8_rtrim($str, $charlist = '')
    {
        return \dokuwiki\Utf8\PhpString::rtrim($str, $charlist);
    }
}

if (!function_exists('utf8_trim')) {
    function utf8_trim($str, $charlist = '')
    {
        return \dokuwiki\Utf8\PhpString::trim($str, $charlist);
    }
}

if (!function_exists('utf8_strtolower')) {
    function utf8_strtolower($str)
    {
        return \dokuwiki\Utf8\PhpString::strtolower($str);
    }
}

if (!function_exists('utf8_strtoupper')) {
    function utf8_strtoupper($str)
    {
        return \dokuwiki\Utf8\PhpString::strtoupper($str);
    }
}

if (!function_exists('utf8_ucfirst')) {
    function utf8_ucfirst($str)
    {
        return \dokuwiki\Utf8\PhpString::ucfirst($str);
    }
}

if (!function_exists('utf8_ucwords')) {
    function utf8_ucwords($str)
    {
        return \dokuwiki\Utf8\PhpString::ucwords($str);
    }
}

if (!function_exists('utf8_deaccent')) {
    function utf8_deaccent($str, $case = 0)
    {
        return \dokuwiki\Utf8\Clean::deaccent($str, $case);
    }
}

if (!function_exists('utf8_romanize')) {
    function utf8_romanize($str)
    {
        return \dokuwiki\Utf8\Clean::romanize($str);
    }
}

if (!function_exists('utf8_stripspecials')) {
    function utf8_stripspecials($str, $repl = '', $additional = '')
    {
        return \dokuwiki\Utf8\Clean::stripspecials($str, $repl, $additional);
    }
}

if (!function_exists('utf8_strpos')) {
    function utf8_strpos($haystack, $needle, $offset = 0)
    {
        return \dokuwiki\Utf8\PhpString::strpos($haystack, $needle, $offset);
    }
}

if (!function_exists('utf8_tohtml')) {
    function utf8_tohtml($str)
    {
        return \dokuwiki\Utf8\Conversion::toHtml($str);
    }
}

if (!function_exists('utf8_unhtml')) {
    function utf8_unhtml($str, $enties = false)
    {
        return \dokuwiki\Utf8\Conversion::fromHtml($str, $enties);
    }
}

if (!function_exists('utf8_to_unicode')) {
    function utf8_to_unicode($str, $strict = false)
    {
        return \dokuwiki\Utf8\Unicode::fromUtf8($str, $strict);
    }
}

if (!function_exists('unicode_to_utf8')) {
    function unicode_to_utf8($arr, $strict = false)
    {
        return \dokuwiki\Utf8\Unicode::toUtf8($arr, $strict);
    }
}

if (!function_exists('utf8_to_utf16be')) {
    function utf8_to_utf16be($str, $bom = false)
    {
        return \dokuwiki\Utf8\Conversion::toUtf16be($str, $bom);
    }
}

if (!function_exists('utf16be_to_utf8')) {
    function utf16be_to_utf8($str)
    {
        return \dokuwiki\Utf8\Conversion::fromUtf16be($str);
    }
}

if (!function_exists('utf8_bad_replace')) {
    function utf8_bad_replace($str, $replace = '')
    {
        return \dokuwiki\Utf8\Clean::replaceBadBytes($str, $replace);
    }
}

if (!function_exists('utf8_correctIdx')) {
    function utf8_correctIdx($str, $i, $next = false)
    {
        return \dokuwiki\Utf8\Clean::correctIdx($str, $i, $next);
    }
}
