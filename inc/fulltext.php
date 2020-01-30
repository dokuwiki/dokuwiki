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
    dbg_deprecated(FulltextSearch::class.'::pageSearch()');
    return FulltextSearch::pageSearch($query, $highlight, $sort, $after, $before);
}

/** @deprecated 2019-12-28 */
function ft_snippet($id, $highlight) {
    dbg_deprecated(FulltextSearch::class.'::snippet()');
    return FulltextSearch::snippet($id, $highlight);
}

/** @deprecated 2019-12-28 */
function ft_snippet_re_preprocess($term) {
    dbg_deprecated(FulltextSearch::class.'::snippetRePreprocess()');
    return FulltextSearch::snippetRePreprocess($term);
}

/** @deprecated 2019-12-28 */
function ft_queryParser($Indexer, $query) {
    dbg_deprecated(QueryParser::class.'::convert()');
    return QueryParser::convert($query);
}

/** @deprecated 2019-12-28 */
function ft_queryUnparser_simple(array $and, array $not, array $phrases, array $ns, array $notns) {
    dbg_deprecated(QueryParser::class.'::revert()');
    return QueryParser::revert($and, $not, $phrases, $ns, $notns);
}


/**
 * Functions for metadata lookups
 */
/** @deprecated 2019-12-28 */
function ft_pageLookup($id, $in_ns=false, $in_title=false, $after = null, $before = null) {
    dbg_deprecated(MetadataSearch::class.'::pageLookup()');
    return MetadataSearch::pageLookup($id, $in_ns, $in_title, $after, $before);
}

/** @deprecated 2019-12-28 */
function ft_backlinks($id, $ignore_perms = false) {
    dbg_deprecated(MetadataSearch::class.'::backlinks()');
    return MetadataSearch::backlinks($id, $ignore_perms);
}

/** @deprecated 2019-12-28 */
function ft_mediause($id, $ignore_perms = false) {
    dbg_deprecated(MetadataSearch::class.'::mediause()');
    return MetadataSearch::mediause($id, $ignore_perms);
}

//Setup VIM: ex: et ts=4 :
