<?php
/**
 * DokuWiki fulltextsearch functions using the index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Search\FulltextSearch;
use dokuwiki\Search\MetaSearch;


/**
 * create snippets for the first few results only
 */
if(!defined('FT_SNIPPET_NUMBER')) define('FT_SNIPPET_NUMBER',15);


/**
 * Functions for Fulltext Search
 */
function ft_pageSearch($query, &$highlight, $sort = null, $after = null, $before = null) {
    dbg_deprecated('ft_pageSearch');
    return FulltextSearch::pageSearch($query, $highlight, $sort, $after, $before);
}

function _ft_pageSearch(&$data) {
    dbg_deprecated('_ft_pageSearch');
    return FulltextSearch::callback_pageSearch($data);
}

function ft_pageLookup($id, $in_ns=false, $in_title=false, $after = null, $before = null) {
    dbg_deprecated('ft_pageLookup');
    return FulltextSearch::pageLookup($id, $in_ns, $in_title, $after, $before);
}

function _ft_pageLookup(&$data) {
    dbg_deprecated('_ft_pageLookup');
    return FulltextSearch::callback_pageLookup($data);
}

function _ft_filterResultsByTime(array $results, $after, $before) {
    dbg_deprecated('_ft_filterResultsByTime');
    return FulltextSearch::filterResultsByTime($results, $after, $before);
}

function _ft_pageLookupTitleCompare($search, $title) {
    dbg_deprecated('_ft_pageLookupTitleCompare');
    return FulltextSearch::pageLookupTitleCompare($search, $title);
}

function ft_pagesorter($a, $b) {
    dbg_deprecated('ft_pagesorter');
    return FulltextSearch::pagesorter($a, $b);
}

function ft_pagemtimesorter($a, $b) {
    dbg_deprecated('ft_pagemtimesorter');
    return FulltextSearch::pagemtimesorter($a, $b);
}

function ft_snippet($id, $highlight) {
    dbg_deprecated('ft_snippet');
    return FulltextSearch::snippet($id, $highlight);
}

function ft_snippet_re_preprocess($term) {
    dbg_deprecated('ft_snippet_re_preprocess');
    return FulltextSearch::snippet_re_preprocess($term);
}

function ft_resultCombine($args) {
    dbg_deprecated('ft_resultCombine');
    return FulltextSearch::resultCombine($args);
}

function ft_resultUnite($args) {
    dbg_deprecated('ft_resultUnite');
    return FulltextSearch::resultUnite($args);
}

function ft_resultComplement($args) {
    dbg_deprecated('ft_resultComplement');
    return FulltextSearch::resultComplement($args);
}
function ft_queryParser($Indexer, $query) {
    dbg_deprecated('ft_queryParser');
    return FulltextSearch::queryParser($Indexer, $query);
}

function ft_termParser($Indexer, $term, $consider_asian = true, $phrase_mode = false) {
    dbg_deprecated('ft_termParser');
    return FulltextSearch::termParser($Indexer, $term, $consider_asian, $phrase_mode);
}

function ft_queryUnparser_simple(array $and, array $not, array $phrases, array $ns, array $notns) {
    dbg_deprecated('ft_queryUnparser_simple');
    return FulltextSearch::queryUnparser_simple($and, $not, $phrases, $ns, $notns);
}

/**
 * Functions for metadata lookups
 */
function ft_backlinks($id, $ignore_perms = false) {
    dbg_deprecated('ft_backlinks');
    return MetaSearch::backlinks($id, $ignore_perms);
}

function ft_mediause($id, $ignore_perms = false) {
    dbg_deprecated('ft_mediause');
    return MetaSearch::mediause($id, $ignore_perms);
}

//Setup VIM: ex: et ts=4 :
