<?php
/**
 * DokuWiki sort functions
 *
 * If PHP package "intl" is loaded, then class Collator is used.
 * Otherwise, primitive PHP functions are called (as was done by old code).
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
            dbglog('Collator created with locale "' . $conf['lang'] . '", numeric collation on');
            dbglog('Collator valid locale: "' . $collator->getLocale(Locale::VALID_LOCALE) . '"');
            dbglog('Collator actual locale: "' . $collator->getLocale(Locale::ACTUAL_LOCALE) . '"');
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
    return strnatcmp(utf8_decodeFN($first), utf8_decodeFN($second));
}

/**
 * Replacement for strcmp() in fulltext.php, line 373, where all strings are lowercase.
 * Replacement for strcmp() in search.php, line 371, where all string would be lowercase (function not used anywhere).
 * Replacement for strcasecmp() in Ui/Admin.php, line 162.
 */
function strcompare($first, $second){
    global $collator;
    _init_collator();

    if(!isset($collator))
        return strcasecmp($first, $second);
    else
        return $collator->compare($first, $second);
}

/**
 * Replacement for sort() in fulltext.php, lines 183 and 214,
 *                    and in Ajax.php, line 101.
 */
function sort_pages(&$pages){
    global $collator;
    _init_collator();

    if(!isset($collator))
        sort($pages);
    else
        $collator->sort($pages);
}

/**
 * Replacement for ksort() in Ui/Search.php, line 387.
 */
function sort_keys(&$namespaces_to_hits){
    global $collator;
    _init_collator();

    if(!isset($collator))
        ksort($namespaces_to_hits);
    else
        uksort($namespaces_to_hits, array($collator, 'compare'));
}
