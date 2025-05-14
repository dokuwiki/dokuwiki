<?php

/**
 * DokuWiki fulltextsearch functions using the index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Utf8\Asian;
use dokuwiki\Search\Indexer;
use dokuwiki\Extension\Event;
use dokuwiki\Utf8\Clean;
use dokuwiki\Utf8\PhpString;
use dokuwiki\Utf8\Sort;

/**
 * create snippets for the first few results only
 */
if (!defined('FT_SNIPPET_NUMBER')) define('FT_SNIPPET_NUMBER', 15);

/**
 * The fulltext search
 *
 * Returns a list of matching documents for the given query
 *
 * refactored into ft_pageSearch(), _ft_pageSearch() and trigger_event()
 *
 * @param string     $query
 * @param array      $highlight
 * @param string     $sort
 * @param int|string $after  only show results with mtime after this date, accepts timestap or strtotime arguments
 * @param int|string $before only show results with mtime before this date, accepts timestap or strtotime arguments
 *
 * @return array
 */
function ft_pageSearch($query, &$highlight, $sort = null, $after = null, $before = null)
{

    if ($sort === null) {
        $sort = 'hits';
    }
    $data = [
        'query' => $query,
        'sort' => $sort,
        'after' => $after,
        'before' => $before
    ];
    $data['highlight'] =& $highlight;

    return Event::createAndTrigger('SEARCH_QUERY_FULLPAGE', $data, '_ft_pageSearch');
}

/**
 * Returns a list of matching documents for the given query
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 *
 * @param array $data event data
 * @return array matching documents
 */
function _ft_pageSearch(&$data)
{
    $Indexer = idx_get_indexer();

    // parse the given query
    $q = ft_queryParser($Indexer, $data['query']);
    $data['highlight'] = $q['highlight'];

    if (empty($q['parsed_ary'])) return [];

    // lookup all words found in the query
    $lookup = $Indexer->lookup($q['words']);

    // get all pages in this dokuwiki site (!: includes nonexistent pages)
    $pages_all = [];
    foreach ($Indexer->getPages() as $id) {
        $pages_all[$id] = 0; // base: 0 hit
    }

    // process the query
    $stack = [];
    foreach ($q['parsed_ary'] as $token) {
        switch (substr($token, 0, 3)) {
            case 'W+:':
            case 'W-:':
            case 'W_:': // word
                $word    = substr($token, 3);
                if (isset($lookup[$word])) {
                    $stack[] = (array)$lookup[$word];
                }
                break;
            case 'P+:':
            case 'P-:': // phrase
                $phrase = substr($token, 3);
                // since phrases are always parsed as ((W1)(W2)...(P)),
                // the end($stack) always points the pages that contain
                // all words in this phrase
                $pages  = end($stack);
                $pages_matched = [];
                foreach (array_keys($pages) as $id) {
                    $evdata = [
                        'id' => $id,
                        'phrase' => $phrase,
                        'text' => rawWiki($id)
                    ];
                    $evt = new Event('FULLTEXT_PHRASE_MATCH', $evdata);
                    if ($evt->advise_before() && $evt->result !== true) {
                        $text = PhpString::strtolower($evdata['text']);
                        if (strpos($text, $phrase) !== false) {
                            $evt->result = true;
                        }
                    }
                    $evt->advise_after();
                    if ($evt->result === true) {
                        $pages_matched[$id] = 0; // phrase: always 0 hit
                    }
                }
                $stack[] = $pages_matched;
                break;
            case 'N+:':
            case 'N-:': // namespace
                $ns = cleanID(substr($token, 3)) . ':';
                $pages_matched = [];
                foreach (array_keys($pages_all) as $id) {
                    if (strpos($id, $ns) === 0) {
                        $pages_matched[$id] = 0; // namespace: always 0 hit
                    }
                }
                $stack[] = $pages_matched;
                break;
            case 'AND': // and operation
                $pages = array_splice($stack, -2);
                if ($pages === []) {
                    break;
                }
                $stack[] = ft_resultCombine($pages);
                break;
            case 'OR':  // or operation
                $pages = array_splice($stack, -2);
                if ($pages === []) {
                    break;
                }
                $stack[] = ft_resultUnite($pages);
                break;
            case 'NOT': // not operation (unary)
                $pages   = array_pop($stack);
                $stack[] = ft_resultComplement([$pages_all, $pages]);
                break;
        }
    }
    $docs = array_pop($stack);

    if (empty($docs)) return [];

    // check: settings, acls, existence
    foreach (array_keys($docs) as $id) {
        if (isHiddenPage($id) || auth_quickaclcheck($id) < AUTH_READ || !page_exists($id, '', false)) {
            unset($docs[$id]);
        }
    }

    $docs = _ft_filterResultsByTime($docs, $data['after'], $data['before']);

    if ($data['sort'] === 'mtime') {
        uksort($docs, 'ft_pagemtimesorter');
    } else {
        // sort docs by count
        uksort($docs, 'ft_pagesorter');
        arsort($docs);
    }

    return $docs;
}

/**
 * Returns the backlinks for a given page
 *
 * Uses the metadata index.
 *
 * @param string $id           The id for which links shall be returned
 * @param bool   $ignore_perms Ignore the fact that pages are hidden or read-protected
 * @return array The pages that contain links to the given page
 */
function ft_backlinks($id, $ignore_perms = false)
{
    $result = idx_get_indexer()->lookupKey('relation_references', $id);

    if ($result === []) return $result;

    // check ACL permissions
    foreach (array_keys($result) as $idx) {
        if (
            (!$ignore_perms && (
                isHiddenPage($result[$idx]) || auth_quickaclcheck($result[$idx]) < AUTH_READ
            )) || !page_exists($result[$idx], '', false)
        ) {
            unset($result[$idx]);
        }
    }

    Sort::sort($result);
    return $result;
}

/**
 * Returns the pages that use a given media file
 *
 * Uses the relation media metadata property and the metadata index.
 *
 * Note that before 2013-07-31 the second parameter was the maximum number of results and
 * permissions were ignored. That's why the parameter is now checked to be explicitely set
 * to true (with type bool) in order to be compatible with older uses of the function.
 *
 * @param string $id           The media id to look for
 * @param bool   $ignore_perms Ignore hidden pages and acls (optional, default: false)
 * @return array A list of pages that use the given media file
 */
function ft_mediause($id, $ignore_perms = false)
{
    $result = idx_get_indexer()->lookupKey('relation_media', $id);

    if ($result === []) return $result;

    // check ACL permissions
    foreach (array_keys($result) as $idx) {
        if (
            (!$ignore_perms && (
                    isHiddenPage($result[$idx]) || auth_quickaclcheck($result[$idx]) < AUTH_READ
                )) || !page_exists($result[$idx], '', false)
        ) {
            unset($result[$idx]);
        }
    }

    Sort::sort($result);
    return $result;
}


/**
 * Quicksearch for pagenames
 *
 * By default it only matches the pagename and ignores the
 * namespace. This can be changed with the second parameter.
 * The third parameter allows to search in titles as well.
 *
 * The function always returns titles as well
 *
 * @triggers SEARCH_QUERY_PAGELOOKUP
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 *
 * @param string     $id       page id
 * @param bool       $in_ns    match against namespace as well?
 * @param bool       $in_title search in title?
 * @param int|string $after    only show results with mtime after this date, accepts timestap or strtotime arguments
 * @param int|string $before   only show results with mtime before this date, accepts timestap or strtotime arguments
 *
 * @return string[]
 */
function ft_pageLookup($id, $in_ns = false, $in_title = false, $after = null, $before = null)
{
    $data = [
        'id' => $id,
        'in_ns' => $in_ns,
        'in_title' => $in_title,
        'after' => $after,
        'before' => $before
    ];
    $data['has_titles'] = true; // for plugin backward compatibility check
    return Event::createAndTrigger('SEARCH_QUERY_PAGELOOKUP', $data, '_ft_pageLookup');
}

/**
 * Returns list of pages as array(pageid => First Heading)
 *
 * @param array &$data event data
 * @return string[]
 */
function _ft_pageLookup(&$data)
{
    // split out original parameters
    $id = $data['id'];
    $Indexer = idx_get_indexer();
    $parsedQuery = ft_queryParser($Indexer, $id);
    if (count($parsedQuery['ns']) > 0) {
        $ns = cleanID($parsedQuery['ns'][0]) . ':';
        $id = implode(' ', $parsedQuery['highlight']);
    }
    if (count($parsedQuery['notns']) > 0) {
        $notns = cleanID($parsedQuery['notns'][0]) . ':';
        $id = implode(' ', $parsedQuery['highlight']);
    }

    $in_ns    = $data['in_ns'];
    $in_title = $data['in_title'];
    $cleaned = cleanID($id);

    $Indexer = idx_get_indexer();
    $page_idx = $Indexer->getPages();

    $pages = [];
    if ($id !== '' && $cleaned !== '') {
        foreach ($page_idx as $p_id) {
            if ((strpos($in_ns ? $p_id : noNSorNS($p_id), $cleaned) !== false)) {
                if (!isset($pages[$p_id]))
                    $pages[$p_id] = p_get_first_heading($p_id, METADATA_DONT_RENDER);
            }
        }
        if ($in_title) {
            foreach ($Indexer->lookupKey('title', $id, '_ft_pageLookupTitleCompare') as $p_id) {
                if (!isset($pages[$p_id]))
                    $pages[$p_id] = p_get_first_heading($p_id, METADATA_DONT_RENDER);
            }
        }
    }

    if (isset($ns)) {
        foreach (array_keys($pages) as $p_id) {
            if (strpos($p_id, $ns) !== 0) {
                unset($pages[$p_id]);
            }
        }
    }
    if (isset($notns)) {
        foreach (array_keys($pages) as $p_id) {
            if (strpos($p_id, $notns) === 0) {
                unset($pages[$p_id]);
            }
        }
    }

    // discard hidden pages
    // discard nonexistent pages
    // check ACL permissions
    foreach (array_keys($pages) as $idx) {
        if (
            !isVisiblePage($idx) || !page_exists($idx) ||
            auth_quickaclcheck($idx) < AUTH_READ
        ) {
            unset($pages[$idx]);
        }
    }

    $pages = _ft_filterResultsByTime($pages, $data['after'], $data['before']);

    uksort($pages, 'ft_pagesorter');
    return $pages;
}


/**
 * @param array      $results search results in the form pageid => value
 * @param int|string $after   only returns results with mtime after this date, accepts timestap or strtotime arguments
 * @param int|string $before  only returns results with mtime after this date, accepts timestap or strtotime arguments
 *
 * @return array
 */
function _ft_filterResultsByTime(array $results, $after, $before)
{
    if ($after || $before) {
        $after = is_int($after) ? $after : strtotime($after);
        $before = is_int($before) ? $before : strtotime($before);

        foreach (array_keys($results) as $id) {
            $mTime = filemtime(wikiFN($id));
            if ($after && $after > $mTime) {
                unset($results[$id]);
                continue;
            }
            if ($before && $before < $mTime) {
                unset($results[$id]);
            }
        }
    }

    return $results;
}

/**
 * Tiny helper function for comparing the searched title with the title
 * from the search index. This function is a wrapper around stripos with
 * adapted argument order and return value.
 *
 * @param string $search searched title
 * @param string $title  title from index
 * @return bool
 */
function _ft_pageLookupTitleCompare($search, $title)
{
    if (Clean::isASCII($search)) {
        $pos = stripos($title, $search);
    } else {
        $pos = PhpString::strpos(
            PhpString::strtolower($title),
            PhpString::strtolower($search)
        );
    }

    return $pos !== false;
}

/**
 * Sort pages based on their namespace level first, then on their string
 * values. This makes higher hierarchy pages rank higher than lower hierarchy
 * pages.
 *
 * @param string $a
 * @param string $b
 * @return int Returns < 0 if $a is less than $b; > 0 if $a is greater than $b, and 0 if they are equal.
 */
function ft_pagesorter($a, $b)
{
    $ac = count(explode(':', $a));
    $bc = count(explode(':', $b));
    if ($ac < $bc) {
        return -1;
    } elseif ($ac > $bc) {
        return 1;
    }
    return Sort::strcmp($a, $b);
}

/**
 * Sort pages by their mtime, from newest to oldest
 *
 * @param string $a
 * @param string $b
 *
 * @return int Returns < 0 if $a is newer than $b, > 0 if $b is newer than $a and 0 if they are of the same age
 */
function ft_pagemtimesorter($a, $b)
{
    $mtimeA = filemtime(wikiFN($a));
    $mtimeB = filemtime(wikiFN($b));
    return $mtimeB - $mtimeA;
}

/**
 * Creates a snippet extract
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @triggers FULLTEXT_SNIPPET_CREATE
 *
 * @param string $id page id
 * @param array $highlight
 * @return mixed
 */
function ft_snippet($id, $highlight)
{
    $text = rawWiki($id);
    $text = str_replace("\xC2\xAD", '', $text);
     // remove soft-hyphens
    $evdata = [
        'id'        => $id,
        'text'      => &$text,
        'highlight' => &$highlight,
        'snippet'   => ''
    ];

    $evt = new Event('FULLTEXT_SNIPPET_CREATE', $evdata);
    if ($evt->advise_before()) {
        $match = [];
        $snippets = [];
        $utf8_offset = 0;
        $offset = 0;
        $end = 0;
        $len = PhpString::strlen($text);

        // build a regexp from the phrases to highlight
        $re1 = '(' .
            implode(
                '|',
                array_map(
                    'ft_snippet_re_preprocess',
                    array_map(
                        'preg_quote_cb',
                        array_filter((array) $highlight)
                    )
                )
            ) .
            ')';
        $re2 = "$re1.{0,75}(?!\\1)$re1";
        $re3 = "$re1.{0,45}(?!\\1)$re1.{0,45}(?!\\1)(?!\\2)$re1";

        for ($cnt = 4; $cnt--;) {
            if (0) {
            } elseif (preg_match('/' . $re3 . '/iu', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
            } elseif (preg_match('/' . $re2 . '/iu', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
            } elseif (preg_match('/' . $re1 . '/iu', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
            } else {
                break;
            }

            [$str, $idx] = $match[0];

            // convert $idx (a byte offset) into a utf8 character offset
            $utf8_idx = PhpString::strlen(substr($text, 0, $idx));
            $utf8_len = PhpString::strlen($str);

            // establish context, 100 bytes surrounding the match string
            // first look to see if we can go 100 either side,
            // then drop to 50 adding any excess if the other side can't go to 50,
            $pre = min($utf8_idx - $utf8_offset, 100);
            $post = min($len - $utf8_idx - $utf8_len, 100);

            if ($pre > 50 && $post > 50) {
                $pre = 50;
                $post = 50;
            } elseif ($pre > 50) {
                $pre = min($pre, 100 - $post);
            } elseif ($post > 50) {
                $post = min($post, 100 - $pre);
            } elseif ($offset == 0) {
                // both are less than 50, means the context is the whole string
                // make it so and break out of this loop - there is no need for the
                // complex snippet calculations
                $snippets = [$text];
                break;
            }

            // establish context start and end points, try to append to previous
            // context if possible
            $start = $utf8_idx - $pre;
            $append = ($start < $end) ? $end : false;  // still the end of the previous context snippet
            $end = $utf8_idx + $utf8_len + $post;      // now set it to the end of this context

            if ($append) {
                $snippets[count($snippets) - 1] .= PhpString::substr($text, $append, $end - $append);
            } else {
                $snippets[] = PhpString::substr($text, $start, $end - $start);
            }

            // set $offset for next match attempt
            // continue matching after the current match
            // if the current match is not the longest possible match starting at the current offset
            // this prevents further matching of this snippet but for possible matches of length
            // smaller than match length + context (at least 50 characters) this match is part of the context
            $utf8_offset = $utf8_idx + $utf8_len;
            $offset = $idx + strlen(PhpString::substr($text, $utf8_idx, $utf8_len));
            $offset = Clean::correctIdx($text, $offset);
        }

        $m = "\1";
        $snippets = preg_replace('/' . $re1 . '/iu', $m . '$1' . $m, $snippets);
        $snippet = preg_replace(
            '/' . $m . '([^' . $m . ']*?)' . $m . '/iu',
            '<strong class="search_hit">$1</strong>',
            hsc(implode('... ', $snippets))
        );

        $evdata['snippet'] = $snippet;
    }
    $evt->advise_after();
    unset($evt);

    return $evdata['snippet'];
}

/**
 * Wraps a search term in regex boundary checks.
 *
 * @param string $term
 * @return string
 */
function ft_snippet_re_preprocess($term)
{
    // do not process asian terms where word boundaries are not explicit
    if (Asian::isAsianWords($term)) return $term;

    if (UTF8_PROPERTYSUPPORT) {
        // unicode word boundaries
        // see http://stackoverflow.com/a/2449017/172068
        $BL = '(?<!\pL)';
        $BR = '(?!\pL)';
    } else {
        // not as correct as above, but at least won't break
        $BL = '\b';
        $BR = '\b';
    }

    if (str_starts_with($term, '\\*')) {
        $term = substr($term, 2);
    } else {
        $term = $BL . $term;
    }

    if (str_ends_with($term, '\\*')) {
        $term = substr($term, 0, -2);
    } else {
        $term .= $BR;
    }

    if ($term == $BL || $term == $BR || $term == $BL . $BR) $term = '';
    return $term;
}

/**
 * Combine found documents and sum up their scores
 *
 * This function is used to combine searched words with a logical
 * AND. Only documents available in all arrays are returned.
 *
 * based upon PEAR's PHP_Compat function for array_intersect_key()
 *
 * @param array $args An array of page arrays
 * @return array
 */
function ft_resultCombine($args)
{
    $array_count = count($args);
    if ($array_count == 1) {
        return $args[0];
    }

    $result = [];
    if ($array_count > 1) {
        foreach ($args[0] as $key => $value) {
            $result[$key] = $value;
            for ($i = 1; $i !== $array_count; $i++) {
                if (!isset($args[$i][$key])) {
                    unset($result[$key]);
                    break;
                }
                $result[$key] += $args[$i][$key];
            }
        }
    }
    return $result;
}

/**
 * Unites found documents and sum up their scores
 *
 * based upon ft_resultCombine() function
 *
 * @param array $args An array of page arrays
 * @return array
 *
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 */
function ft_resultUnite($args)
{
    $array_count = count($args);
    if ($array_count === 1) {
        return $args[0];
    }

    $result = $args[0];
    for ($i = 1; $i !== $array_count; $i++) {
        foreach (array_keys($args[$i]) as $id) {
            $result[$id] += $args[$i][$id];
        }
    }
    return $result;
}

/**
 * Computes the difference of documents using page id for comparison
 *
 * nearly identical to PHP5's array_diff_key()
 *
 * @param array $args An array of page arrays
 * @return array
 *
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 */
function ft_resultComplement($args)
{
    $array_count = count($args);
    if ($array_count === 1) {
        return $args[0];
    }

    $result = $args[0];
    foreach (array_keys($result) as $id) {
        for ($i = 1; $i !== $array_count; $i++) {
            if (isset($args[$i][$id])) unset($result[$id]);
        }
    }
    return $result;
}

/**
 * Parses a search query and builds an array of search formulas
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 *
 * @param Indexer $Indexer
 * @param string                  $query search query
 * @return array of search formulas
 */
function ft_queryParser($Indexer, $query)
{
    /**
     * parse a search query and transform it into intermediate representation
     *
     * in a search query, you can use the following expressions:
     *
     *   words:
     *     include
     *     -exclude
     *   phrases:
     *     "phrase to be included"
     *     -"phrase you want to exclude"
     *   namespaces:
     *     @include:namespace (or ns:include:namespace)
     *     ^exclude:namespace (or -ns:exclude:namespace)
     *   groups:
     *     ()
     *     -()
     *   operators:
     *     and ('and' is the default operator: you can always omit this)
     *     or  (or pipe symbol '|', lower precedence than 'and')
     *
     * e.g. a query [ aa "bb cc" @dd:ee ] means "search pages which contain
     *      a word 'aa', a phrase 'bb cc' and are within a namespace 'dd:ee'".
     *      this query is equivalent to [ -(-aa or -"bb cc" or -ns:dd:ee) ]
     *      as long as you don't mind hit counts.
     *
     * intermediate representation consists of the following parts:
     *
     *   ( )           - group
     *   AND           - logical and
     *   OR            - logical or
     *   NOT           - logical not
     *   W+:, W-:, W_: - word      (underscore: no need to highlight)
     *   P+:, P-:      - phrase    (minus sign: logically in NOT group)
     *   N+:, N-:      - namespace
     */
    $parsed_query = '';
    $parens_level = 0;
    $terms = preg_split(
        '/(-?".*?")/u',
        PhpString::strtolower($query),
        -1,
        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );

    foreach ($terms as $term) {
        $parsed = '';
        if (preg_match('/^(-?)"(.+)"$/u', $term, $matches)) {
            // phrase-include and phrase-exclude
            $not = $matches[1] ? 'NOT' : '';
            $parsed = $not . ft_termParser($Indexer, $matches[2], false, true);
        } else {
            // fix incomplete phrase
            $term = str_replace('"', ' ', $term);

            // fix parentheses
            $term = str_replace(')', ' ) ', $term);
            $term = str_replace('(', ' ( ', $term);
            $term = str_replace('- (', ' -(', $term);

            // treat pipe symbols as 'OR' operators
            $term = str_replace('|', ' or ', $term);

            // treat ideographic spaces (U+3000) as search term separators
            // FIXME: some more separators?
            $term = preg_replace('/[ \x{3000}]+/u', ' ', $term);
            $term = trim($term);
            if ($term === '') continue;

            $tokens = explode(' ', $term);
            foreach ($tokens as $token) {
                if ($token === '(') {
                    // parenthesis-include-open
                    $parsed .= '(';
                    ++$parens_level;
                } elseif ($token === '-(') {
                    // parenthesis-exclude-open
                    $parsed .= 'NOT(';
                    ++$parens_level;
                } elseif ($token === ')') {
                    // parenthesis-any-close
                    if ($parens_level === 0) continue;
                    $parsed .= ')';
                    $parens_level--;
                } elseif ($token === 'and') {
                    // logical-and (do nothing)
                } elseif ($token === 'or') {
                    // logical-or
                    $parsed .= 'OR';
                } elseif (preg_match('/^(?:\^|-ns:)(.+)$/u', $token, $matches)) {
                    // namespace-exclude
                    $parsed .= 'NOT(N+:' . $matches[1] . ')';
                } elseif (preg_match('/^(?:@|ns:)(.+)$/u', $token, $matches)) {
                    // namespace-include
                    $parsed .= '(N+:' . $matches[1] . ')';
                } elseif (preg_match('/^-(.+)$/', $token, $matches)) {
                    // word-exclude
                    $parsed .= 'NOT(' . ft_termParser($Indexer, $matches[1]) . ')';
                } else {
                    // word-include
                    $parsed .= ft_termParser($Indexer, $token);
                }
            }
        }
        $parsed_query .= $parsed;
    }

    // cleanup (very sensitive)
    $parsed_query .= str_repeat(')', $parens_level);
    do {
        $parsed_query_old = $parsed_query;
        $parsed_query = preg_replace('/(NOT)?\(\)/u', '', $parsed_query);
    } while ($parsed_query !== $parsed_query_old);
    $parsed_query = preg_replace('/(NOT|OR)+\)/u', ')', $parsed_query);
    $parsed_query = preg_replace('/(OR)+/u', 'OR', $parsed_query);
    $parsed_query = preg_replace('/\(OR/u', '(', $parsed_query);
    $parsed_query = preg_replace('/^OR|OR$/u', '', $parsed_query);
    $parsed_query = preg_replace('/\)(NOT)?\(/u', ')AND$1(', $parsed_query);

    // adjustment: make highlightings right
    $parens_level     = 0;
    $notgrp_levels    = [];
    $parsed_query_new = '';
    $tokens = preg_split('/(NOT\(|[()])/u', $parsed_query, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $token) {
        if ($token === 'NOT(') {
            $notgrp_levels[] = ++$parens_level;
        } elseif ($token === '(') {
            ++$parens_level;
        } elseif ($token === ')') {
            if ($parens_level-- === end($notgrp_levels)) array_pop($notgrp_levels);
        } elseif (count($notgrp_levels) % 2 === 1) {
            // turn highlight-flag off if terms are logically in "NOT" group
            $token = preg_replace('/([WPN])\+\:/u', '$1-:', $token);
        }
        $parsed_query_new .= $token;
    }
    $parsed_query = $parsed_query_new;

    /**
     * convert infix notation string into postfix (Reverse Polish notation) array
     * by Shunting-yard algorithm
     *
     * see: http://en.wikipedia.org/wiki/Reverse_Polish_notation
     * see: http://en.wikipedia.org/wiki/Shunting-yard_algorithm
     */
    $parsed_ary     = [];
    $ope_stack      = [];
    $ope_precedence = [')' => 1, 'OR' => 2, 'AND' => 3, 'NOT' => 4, '(' => 5];
    $ope_regex      = '/([()]|OR|AND|NOT)/u';

    $tokens = preg_split($ope_regex, $parsed_query, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $token) {
        if (preg_match($ope_regex, $token)) {
            // operator
            $last_ope = end($ope_stack);
            while ($last_ope !== false && $ope_precedence[$token] <= $ope_precedence[$last_ope] && $last_ope != '(') {
                $parsed_ary[] = array_pop($ope_stack);
                $last_ope = end($ope_stack);
            }
            if ($token == ')') {
                array_pop($ope_stack); // this array_pop always deletes '('
            } else {
                $ope_stack[] = $token;
            }
        } else {
            // operand
            $token_decoded = str_replace(['OP', 'CP'], ['(', ')'], $token);
            $parsed_ary[] = $token_decoded;
        }
    }
    $parsed_ary = array_values([...$parsed_ary, ...array_reverse($ope_stack)]);

    // cleanup: each double "NOT" in RPN array actually does nothing
    $parsed_ary_count = count($parsed_ary);
    for ($i = 1; $i < $parsed_ary_count; ++$i) {
        if ($parsed_ary[$i] === 'NOT' && $parsed_ary[$i - 1] === 'NOT') {
            unset($parsed_ary[$i], $parsed_ary[$i - 1]);
        }
    }
    $parsed_ary = array_values($parsed_ary);

    // build return value
    $q = [];
    $q['query']      = $query;
    $q['parsed_str'] = $parsed_query;
    $q['parsed_ary'] = $parsed_ary;

    foreach ($q['parsed_ary'] as $token) {
        if (strlen($token) < 3 || $token[2] !== ':') continue;
        $body = substr($token, 3);

        switch (substr($token, 0, 3)) {
            case 'N+:':
                     $q['ns'][]        = $body; // for backward compatibility
                break;
            case 'N-:':
                     $q['notns'][]     = $body; // for backward compatibility
                break;
            case 'W_:':
                     $q['words'][]     = $body;
                break;
            case 'W-:':
                     $q['words'][]     = $body;
                     $q['not'][]       = $body; // for backward compatibility
                break;
            case 'W+:':
                     $q['words'][]     = $body;
                     $q['highlight'][] = $body;
                     $q['and'][]       = $body; // for backward compatibility
                break;
            case 'P-:':
                     $q['phrases'][]   = $body;
                break;
            case 'P+:':
                     $q['phrases'][]   = $body;
                     $q['highlight'][] = $body;
                break;
        }
    }
    foreach (['words', 'phrases', 'highlight', 'ns', 'notns', 'and', 'not'] as $key) {
        $q[$key] = empty($q[$key]) ? [] : array_values(array_unique($q[$key]));
    }

    return $q;
}

/**
 * Transforms given search term into intermediate representation
 *
 * This function is used in ft_queryParser() and not for general purpose use.
 *
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 *
 * @param Indexer $Indexer
 * @param string                  $term
 * @param bool                    $consider_asian
 * @param bool                    $phrase_mode
 * @return string
 */
function ft_termParser($Indexer, $term, $consider_asian = true, $phrase_mode = false)
{
    $parsed = '';
    if ($consider_asian) {
        // successive asian characters need to be searched as a phrase
        $words = Asian::splitAsianWords($term);
        foreach ($words as $word) {
            $phrase_mode = $phrase_mode ? true : Asian::isAsianWords($word);
            $parsed .= ft_termParser($Indexer, $word, false, $phrase_mode);
        }
    } else {
        $term_noparen = str_replace(['(', ')'], ' ', $term);
        $words = $Indexer->tokenizer($term_noparen, true);

        // W_: no need to highlight
        if (empty($words)) {
            $parsed = '()'; // important: do not remove
        } elseif ($words[0] === $term) {
            $parsed = '(W+:' . $words[0] . ')';
        } elseif ($phrase_mode) {
            $term_encoded = str_replace(['(', ')'], ['OP', 'CP'], $term);
            $parsed = '((W_:' . implode(')(W_:', $words) . ')(P+:' . $term_encoded . '))';
        } else {
            $parsed = '((W+:' . implode(')(W+:', $words) . '))';
        }
    }
    return $parsed;
}

/**
 * Recreate a search query string based on parsed parts, doesn't support negated phrases and `OR` searches
 *
 * @param array $and
 * @param array $not
 * @param array $phrases
 * @param array $ns
 * @param array $notns
 *
 * @return string
 */
function ft_queryUnparser_simple(array $and, array $not, array $phrases, array $ns, array $notns)
{
    $query = implode(' ', $and);
    if ($not !== []) {
        $query .= ' -' . implode(' -', $not);
    }

    if ($phrases !== []) {
        $query .= ' "' . implode('" "', $phrases) . '"';
    }

    if ($ns !== []) {
        $query .= ' @' . implode(' @', $ns);
    }

    if ($notns !== []) {
        $query .= ' ^' . implode(' ^', $notns);
    }

    return $query;
}

//Setup VIM: ex: et ts=4 :
