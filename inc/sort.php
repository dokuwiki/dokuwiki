<?php
/**
 * DokuWiki sort functions
 *
 * When "intl" extension is available, all sorts are done using a collator.
 * Otherwise, primitive PHP functions are called.
 *
 * The collator is created using the locale given in $conf['lang'].
 * It always uses case insensitive "natural" ordering in its collation.
 * The fallback solution uses the primitive PHP functions that return the same results.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Moisés Braga Ribeiro <moisesbr@gmail.com>
 */

/* @var bool $intl_extension_available */
$intl_extension_available = class_exists('Collator');
/* @var Collator $collator */
$collator = null;

/**
 * Initialize collator using $conf['lang'].
 * The initialization is done only once.
 * The collation takes "natural ordering" into account, so "page 2" is before "page 10".
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function _init_collator() {
    global $conf, $collator, $intl_extension_available;

    if ($intl_extension_available && !isset($collator)) {
        $collator = Collator::create($conf['lang']);
        if (isset($collator)) {
            $collator->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);
            dbglog('Collator created with locale "' . $conf['lang'] . '": numeric collation on, ' .
                   'valid locale "' . $collator->getLocale(Locale::VALID_LOCALE) . '", ' .
                   'actual locale "' . $collator->getLocale(Locale::ACTUAL_LOCALE) . '"');
        }
    }
}

/**
 * Drop-in replacement for string comparison functions.
 * It uses a collator-based comparison, or strnatcasecmp() as a fallback.
 *
 * Replacement for strcmp() in fulltext.php, line 373.
 * Replacement for strcmp() in search.php, line 371.
 * Replacement for strcasecmp() in Ui/Admin.php, line 162.
 *
 * @param string $first  first string to compare
 * @param string $second second string to compare
 * @return int negative value if $first is before $second; positive value if $first is after $second; zero if they are equal
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function compare($first, $second) {
    global $collator;
    _init_collator();

    if (isset($collator))
        return $collator->compare($first, $second);
    else
        return strnatcasecmp($first, $second);
}

/**
 * Drop-in replacement for sort() when the parameter is an array of page names or generic strings.
 * It uses a collator-based sort, or sort() with flags SORT_NATURAL and SORT_FLAG_CASE as a fallback.
 *
 * Replacement for sort() in fulltext.php, lines 183 and 214.
 * Replacement for sort() in Ajax.php, line 101.
 *
 * @param string $pagenames the array to be sorted
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function sort_pagenames(&$pagenames) {
    global $collator;
    _init_collator();

    if (isset($collator))
        $collator->sort($pagenames);
    else
        sort($pagenames, SORT_NATURAL | SORT_FLAG_CASE);
}

/**
 * Drop-in replacement for ksort() when the parameter is an associative array with page names as keys.
 * It uses a collator-based sort, or ksort() with flags SORT_NATURAL and SORT_FLAG_CASE as a fallback.
 *
 * Replacement for ksort() in Ui/Search.php, line 387.
 *
 * @param string $keys the associative array to be sorted
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function sort_keys(&$keys) {
    global $collator;
    _init_collator();

    if (isset($collator))
        uksort($keys, array($collator, 'compare'));
    else
        ksort($keys, SORT_NATURAL | SORT_FLAG_CASE);
}

/**
 * Drop-in replacement for natsort() when the parameter is an array of file names.
 * Filenames may not be equal to page names, depending on the setting in $conf['fnencode'],
 * so the correct behavior is to sort page names and reflect this sorting in the filename array.
 *
 * Replacement for natsort() in search.php, lines 52 and 54.
 *
 * @param string $filenames the array to be sorted
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function sort_filenames(&$filenames) {
    global $collator;
    _init_collator();

    if (isset($collator))
        return uasort($filenames, '_sort_filenames_with_collator');
    else
        return uasort($filenames, '_sort_filenames_without_collator');
}

/**
 * Collator-based string comparison for filenames.
 * The filenames are converted to page names with utf8_decodeFN() before the comparison.
 * 
 * @param string $first  first filename to compare
 * @param string $second second filename to compare
 * @return int negative value if $first is before $second; positive value if $first is after $second; zero if they are equal
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function _sort_filenames_with_collator($first, $second) {
    global $collator;
    return $collator->compare(utf8_decodeFN($first), utf8_decodeFN($second));
}

/**
 * Fallback string comparison for filenames, using strnatcasecmp().
 * The filenames are converted to page names with utf8_decodeFN() before the comparison.
 * 
 * @param string $first  first filename to compare
 * @param string $second second filename to compare
 * @return int negative value if $first is before $second; positive value if $first is after $second; zero if they are equal
 *
 * @author Moisés Braga Ribeiro <moisesbr@gmail.com>
 */
function _sort_filenames_without_collator($first, $second) {
    return strnatcasecmp(utf8_decodeFN($first), utf8_decodeFN($second));
}
