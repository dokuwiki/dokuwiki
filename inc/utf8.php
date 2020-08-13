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
    /** @deprecated 2019-06-09 */
    function utf8_isASCII($str)
    {
        dbg_deprecated(Clean::class . '::isASCII()');
        return Clean::isASCII($str);
    }
}


if (!function_exists('utf8_strip')) {
    /** @deprecated 2019-06-09 */
    function utf8_strip($str)
    {
        dbg_deprecated(Clean::class . '::strip()');
        return Clean::strip($str);
    }
}

if (!function_exists('utf8_check')) {
    /** @deprecated 2019-06-09 */
    function utf8_check($str)
    {
        dbg_deprecated(Clean::class . '::isUtf8()');
        return Clean::isUtf8($str);
    }
}

if (!function_exists('utf8_basename')) {
    /** @deprecated 2019-06-09 */
    function utf8_basename($path, $suffix = '')
    {
        dbg_deprecated(PhpString::class . '::basename()');
        return PhpString::basename($path, $suffix);
    }
}

if (!function_exists('utf8_strlen')) {
    /** @deprecated 2019-06-09 */
    function utf8_strlen($str)
    {
        dbg_deprecated(PhpString::class . '::strlen()');
        return PhpString::strlen($str);
    }
}

if (!function_exists('utf8_substr')) {
    /** @deprecated 2019-06-09 */
    function utf8_substr($str, $offset, $length = null)
    {
        dbg_deprecated(PhpString::class . '::substr()');
        return PhpString::substr($str, $offset, $length);
    }
}

if (!function_exists('utf8_substr_replace')) {
    /** @deprecated 2019-06-09 */
    function utf8_substr_replace($string, $replacement, $start, $length = 0)
    {
        dbg_deprecated(PhpString::class . '::substr_replace()');
        return PhpString::substr_replace($string, $replacement, $start, $length);
    }
}

if (!function_exists('utf8_ltrim')) {
    /** @deprecated 2019-06-09 */
    function utf8_ltrim($str, $charlist = '')
    {
        dbg_deprecated(PhpString::class . '::ltrim()');
        return PhpString::ltrim($str, $charlist);
    }
}

if (!function_exists('utf8_rtrim')) {
    /** @deprecated 2019-06-09 */
    function utf8_rtrim($str, $charlist = '')
    {
        dbg_deprecated(PhpString::class . '::rtrim()');
        return PhpString::rtrim($str, $charlist);
    }
}

if (!function_exists('utf8_trim')) {
    /** @deprecated 2019-06-09 */
    function utf8_trim($str, $charlist = '')
    {
        dbg_deprecated(PhpString::class . '::trim()');
        return PhpString::trim($str, $charlist);
    }
}

if (!function_exists('utf8_strtolower')) {
    /** @deprecated 2019-06-09 */
    function utf8_strtolower($str)
    {
        dbg_deprecated(PhpString::class . '::strtolower()');
        return PhpString::strtolower($str);
    }
}

if (!function_exists('utf8_strtoupper')) {
    /** @deprecated 2019-06-09 */
    function utf8_strtoupper($str)
    {
        dbg_deprecated(PhpString::class . '::strtoupper()');
        return PhpString::strtoupper($str);
    }
}

if (!function_exists('utf8_ucfirst')) {
    /** @deprecated 2019-06-09 */
    function utf8_ucfirst($str)
    {
        dbg_deprecated(PhpString::class . '::ucfirst()');
        return PhpString::ucfirst($str);
    }
}

if (!function_exists('utf8_ucwords')) {
    /** @deprecated 2019-06-09 */
    function utf8_ucwords($str)
    {
        dbg_deprecated(PhpString::class . '::ucwords()');
        return PhpString::ucwords($str);
    }
}

if (!function_exists('utf8_deaccent')) {
    /** @deprecated 2019-06-09 */
    function utf8_deaccent($str, $case = 0)
    {
        dbg_deprecated(Clean::class . '::deaccent()');
        return Clean::deaccent($str, $case);
    }
}

if (!function_exists('utf8_romanize')) {
    /** @deprecated 2019-06-09 */
    function utf8_romanize($str)
    {
        dbg_deprecated(Clean::class . '::romanize()');
        return Clean::romanize($str);
    }
}

if (!function_exists('utf8_stripspecials')) {
    /** @deprecated 2019-06-09 */
    function utf8_stripspecials($str, $repl = '', $additional = '')
    {
        dbg_deprecated(Clean::class . '::stripspecials()');
        return Clean::stripspecials($str, $repl, $additional);
    }
}

if (!function_exists('utf8_strpos')) {
    /** @deprecated 2019-06-09 */
    function utf8_strpos($haystack, $needle, $offset = 0)
    {
        dbg_deprecated(PhpString::class . '::strpos()');
        return PhpString::strpos($haystack, $needle, $offset);
    }
}

if (!function_exists('utf8_tohtml')) {
    /** @deprecated 2019-06-09 */
    function utf8_tohtml($str, $all = false)
    {
        dbg_deprecated(Conversion::class . '::toHtml()');
        return Conversion::toHtml($str, $all);
    }
}

if (!function_exists('utf8_unhtml')) {
    /** @deprecated 2019-06-09 */
    function utf8_unhtml($str, $enties = false)
    {
        dbg_deprecated(Conversion::class . '::fromHtml()');
        return Conversion::fromHtml($str, $enties);
    }
}

if (!function_exists('utf8_to_unicode')) {
    /** @deprecated 2019-06-09 */
    function utf8_to_unicode($str, $strict = false)
    {
        dbg_deprecated(Unicode::class . '::fromUtf8()');
        return Unicode::fromUtf8($str, $strict);
    }
}

if (!function_exists('unicode_to_utf8')) {
    /** @deprecated 2019-06-09 */
    function unicode_to_utf8($arr, $strict = false)
    {
        dbg_deprecated(Unicode::class . '::toUtf8()');
        return Unicode::toUtf8($arr, $strict);
    }
}

if (!function_exists('utf8_to_utf16be')) {
    /** @deprecated 2019-06-09 */
    function utf8_to_utf16be($str, $bom = false)
    {
        dbg_deprecated(Conversion::class . '::toUtf16be()');
        return Conversion::toUtf16be($str, $bom);
    }
}

if (!function_exists('utf16be_to_utf8')) {
    /** @deprecated 2019-06-09 */
    function utf16be_to_utf8($str)
    {
        dbg_deprecated(Conversion::class . '::fromUtf16be()');
        return Conversion::fromUtf16be($str);
    }
}

if (!function_exists('utf8_bad_replace')) {
    /** @deprecated 2019-06-09 */
    function utf8_bad_replace($str, $replace = '')
    {
        dbg_deprecated(Clean::class . '::replaceBadBytes()');
        return Clean::replaceBadBytes($str, $replace);
    }
}

if (!function_exists('utf8_correctIdx')) {
    /** @deprecated 2019-06-09 */
    function utf8_correctIdx($str, $i, $next = false)
    {
        dbg_deprecated(Clean::class . '::correctIdx()');
        return Clean::correctIdx($str, $i, $next);
    }
}
