<?php
/**
 * UTF8 helper functions
 *
 * This file now only intitializes the UTF-8 capability detection and defines helper
 * functions if needed. All actual code is in the \dokuwiki\Utf8 classes
 *
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Utf8\Clean;
use dokuwiki\Utf8\Conversion;
use dokuwiki\Utf8\PhpString;
use dokuwiki\Utf8\Unicode;

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
        return Clean::isASCII($str);
    }
}


if (!function_exists('utf8_strip')) {
    function utf8_strip($str)
    {
        return Clean::strip($str);
    }
}

if (!function_exists('utf8_check')) {
    function utf8_check($str)
    {
        return Clean::isUtf8($str);
    }
}

if (!function_exists('utf8_basename')) {
    function utf8_basename($path, $suffix = '')
    {
        return PhpString::basename($path, $suffix);
    }
}

if (!function_exists('utf8_strlen')) {
    function utf8_strlen($str)
    {
        return PhpString::strlen($str);
    }
}

if (!function_exists('utf8_substr')) {
    function utf8_substr($str, $offset, $length = null)
    {
        return PhpString::substr($str, $offset, $length);
    }
}

if (!function_exists('utf8_substr_replace')) {
    function utf8_substr_replace($string, $replacement, $start, $length = 0)
    {
        return PhpString::substr_replace($string, $replacement, $start, $length);
    }
}

if (!function_exists('utf8_ltrim')) {
    function utf8_ltrim($str, $charlist = '')
    {
        return PhpString::ltrim($str, $charlist);
    }
}

if (!function_exists('utf8_rtrim')) {
    function utf8_rtrim($str, $charlist = '')
    {
        return PhpString::rtrim($str, $charlist);
    }
}

if (!function_exists('utf8_trim')) {
    function utf8_trim($str, $charlist = '')
    {
        return PhpString::trim($str, $charlist);
    }
}

if (!function_exists('utf8_strtolower')) {
    function utf8_strtolower($str)
    {
        return PhpString::strtolower($str);
    }
}

if (!function_exists('utf8_strtoupper')) {
    function utf8_strtoupper($str)
    {
        return PhpString::strtoupper($str);
    }
}

if (!function_exists('utf8_ucfirst')) {
    function utf8_ucfirst($str)
    {
        return PhpString::ucfirst($str);
    }
}

if (!function_exists('utf8_ucwords')) {
    function utf8_ucwords($str)
    {
        return PhpString::ucwords($str);
    }
}

if (!function_exists('utf8_deaccent')) {
    function utf8_deaccent($str, $case = 0)
    {
        return Clean::deaccent($str, $case);
    }
}

if (!function_exists('utf8_romanize')) {
    function utf8_romanize($str)
    {
        return Clean::romanize($str);
    }
}

if (!function_exists('utf8_stripspecials')) {
    function utf8_stripspecials($str, $repl = '', $additional = '')
    {
        return Clean::stripspecials($str, $repl, $additional);
    }
}

if (!function_exists('utf8_strpos')) {
    function utf8_strpos($haystack, $needle, $offset = 0)
    {
        return PhpString::strpos($haystack, $needle, $offset);
    }
}

if (!function_exists('utf8_tohtml')) {
    function utf8_tohtml($str)
    {
        return Conversion::toHtml($str);
    }
}

if (!function_exists('utf8_unhtml')) {
    function utf8_unhtml($str, $enties = false)
    {
        return Conversion::fromHtml($str, $enties);
    }
}

if (!function_exists('utf8_to_unicode')) {
    function utf8_to_unicode($str, $strict = false)
    {
        return Unicode::fromUtf8($str, $strict);
    }
}

if (!function_exists('unicode_to_utf8')) {
    function unicode_to_utf8($arr, $strict = false)
    {
        return Unicode::toUtf8($arr, $strict);
    }
}

if (!function_exists('utf8_to_utf16be')) {
    function utf8_to_utf16be($str, $bom = false)
    {
        return Conversion::toUtf16be($str, $bom);
    }
}

if (!function_exists('utf16be_to_utf8')) {
    function utf16be_to_utf8($str)
    {
        return Conversion::fromUtf16be($str);
    }
}

if (!function_exists('utf8_bad_replace')) {
    function utf8_bad_replace($str, $replace = '')
    {
        return Clean::replaceBadBytes($str, $replace);
    }
}

if (!function_exists('utf8_correctIdx')) {
    function utf8_correctIdx($str, $i, $next = false)
    {
        return Clean::correctIdx($str, $i, $next);
    }
}
