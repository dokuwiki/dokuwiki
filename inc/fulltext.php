<?php
/**
 * DokuWiki fulltextsearch functions using the index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Search\FulltextSearch;
use dokuwiki\Search\MetadataSearch;
use dokuwiki\Search\QueryParser;

/**
 * Functions for Fulltext Search
 */
/** @deprecated 2019-12-28 */
function ft_pageSearch($query, &$highlight, $sort = null, $after = null, $before = null) {
    dbg_deprecated('ft_pageSearch');
    return FulltextSearch::pageSearch($query, $highlight, $sort, $after, $before);
}

/** @deprecated 2019-12-28 */
function ft_snippet($id, $highlight) {
    dbg_deprecated('ft_snippet');
    return FulltextSearch::snippet($id, $highlight);
}

/** @deprecated 2019-12-28 */
function ft_snippet_re_preprocess($term) {
    dbg_deprecated('ft_snippet_re_preprocess');
    return FulltextSearch::snippet_re_preprocess($term);
}

/** @deprecated 2019-12-28 */
function ft_queryParser($Indexer, $query) {
    dbg_deprecated('ft_queryParser');
    return QueryParser::convert($query);
}

/** @deprecated 2019-12-28 */
function ft_queryUnparser_simple(array $and, array $not, array $phrases, array $ns, array $notns) {
    dbg_deprecated('ft_queryUnparser_simple');
    return QueryParser::revert_simple($and, $not, $phrases, $ns, $notns);
}


/**

 * Parses a search query and builds an array of search formulas
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 *
 * @param dokuwiki\Search\Indexer $Indexer
 * @param string                  $query search query
 * @return array of search formulas
 */
/** @deprecated 2019-12-28 */
function ft_pageLookup($id, $in_ns=false, $in_title=false, $after = null, $before = null) {
    dbg_deprecated('ft_pageLookup');
    return MetadataSearch::pageLookup($id, $in_ns, $in_title, $after, $before);
}

/**
 * Transforms given search term into intermediate representation
 *
 * This function is used in ft_queryParser() and not for general purpose use.
 *
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 *
 * @param dokuwiki\Search\Indexer $Indexer
 * @param string                  $term
 * @param bool                    $consider_asian
 * @param bool                    $phrase_mode
 * @return string
 */
function ft_termParser($Indexer, $term, $consider_asian = true, $phrase_mode = false) {
    $parsed = '';
    if ($consider_asian) {
        // successive asian characters need to be searched as a phrase
        $words = \dokuwiki\Utf8\Asian::splitAsianWords($term);
        foreach ($words as $word) {
            $phrase_mode = $phrase_mode ? true : \dokuwiki\Utf8\Asian::isAsianWords($word);
            $parsed .= ft_termParser($Indexer, $word, false, $phrase_mode);
        }
    } else {
        $term_noparen = str_replace(array('(', ')'), ' ', $term);
        $words = $Indexer->tokenizer($term_noparen, true);

        // W_: no need to highlight
        if (empty($words)) {
            $parsed = '()'; // important: do not remove
        } elseif ($words[0] === $term) {
            $parsed = '(W+:'.$words[0].')';
        } elseif ($phrase_mode) {
            $term_encoded = str_replace(array('(', ')'), array('OP', 'CP'), $term);
            $parsed = '((W_:'.implode(')(W_:', $words).')(P+:'.$term_encoded.'))';
        } else {
            $parsed = '((W+:'.implode(')(W+:', $words).'))';
        }
    }
    return $parsed;
}

/** @deprecated 2019-12-28 */
function ft_mediause($id, $ignore_perms = false) {
    dbg_deprecated('ft_mediause');
    return MetadataSearch::mediause($id, $ignore_perms);
}

//Setup VIM: ex: et ts=4 :
