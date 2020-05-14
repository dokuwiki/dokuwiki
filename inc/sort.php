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
 */
function natural_sort(&$files_or_dirs){
    global $collator;
    _init_collator();

    if(!isset($collator)){
        natsort($files_or_dirs);
//        echo 'natsort: ' . implode(', ', $files_or_dirs) . '<br/>';
        return;
    }

    $decoded = array();
    for($i = 0; $i < count($files_or_dirs); $i++) {
        $decoded[$i] = utf8_decodeFN($files_or_dirs[$i]);
    }
//    echo 'decoded data: ' . implode(', ', $decoded) . '<br/>';

    $collator->asort($decoded);
//    echo 'collator sort: ' . implode(', ', $decoded) . '<br/>';
    
    $sorted_indexes = array_keys($decoded);
//    echo 'sorted indexes: ' . implode(', ', $sorted_indexes) . '<br/>';
    
    $result = array();
    for($i = 0; $i < count($sorted_indexes); $i++) {
        $result[$i] = $files_or_dirs[$sorted_indexes[$i]];
    }
    for($i = 0; $i < count($files_or_dirs); $i++) {
        $files_or_dirs[$i] = $result[$i];
    }

//    echo 'sorted data: ' . implode(', ', $files_or_dirs) . '<br/>';
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
