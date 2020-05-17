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

$intl_extension_available = class_exists('Collator');
$collator = null;

/**
 * Initialize collator using $conf['lang'].
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
 * Replacement for natsort() in search.php, lines 52 and 54.
 * Actually natsort() was wrongly called in the original code, as file/dir names may not be equal to the page names,
 * depending on the setting in $conf['fnencode'].
 * So the correct behavior is to sort the page names and reflect that sorting in the array with file/dir names.
 */
function sort_filenames(&$filenames) {
    global $collator;
    _init_collator();

    if (isset($collator))
        return uasort($filenames, '_sort_filenames_with_collator');
    else
        return uasort($filenames, '_sort_filenames_without_collator');
}

function _sort_filenames_with_collator($first, $second) {
    global $collator;
    return $collator->compare(utf8_decodeFN($first), utf8_decodeFN($second));
}

function _sort_filenames_without_collator($first, $second) {
    return strnatcasecmp(utf8_decodeFN($first), utf8_decodeFN($second));
}

/**
 * Replacement for strcmp() in fulltext.php, line 373.
 * Replacement for strcmp() in search.php, line 371 (function not used anywhere).
 * Replacement for strcasecmp() in Ui/Admin.php, line 162.
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
 * Replacement for sort() in fulltext.php, lines 183 and 214.
 * Replacement for sort() in Ajax.php, line 101.
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
 * Replacement for ksort() in Ui/Search.php, line 387.
 */
function sort_keys(&$keys) {
    global $collator;
    _init_collator();

    if (isset($collator))
        uksort($keys, array($collator, 'compare'));
    else
        ksort($keys, SORT_NATURAL | SORT_FLAG_CASE);
}
