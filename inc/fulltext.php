<?php
/**
 * DokuWiki fulltextsearch functions using the index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * create snippets for the first few results only
 */
if(!defined('FT_SNIPPET_NUMBER')) define('FT_SNIPPET_NUMBER',15);

/**
 * The fulltext search
 *
 * Returns a list of matching documents for the given query
 *
 * refactored into ft_pageSearch(), _ft_pageSearch() and trigger_event()
 *
 */
function ft_pageSearch($query,&$highlight){

    $data['query'] = $query;
    $data['highlight'] =& $highlight;

    return trigger_event('SEARCH_QUERY_FULLPAGE', $data, '_ft_pageSearch');
}

/**
 * Returns a list of matching documents for the given query
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 */
function _ft_pageSearch(&$data) {
    $Indexer = idx_get_indexer();

    // parse the given query
    $q = ft_queryParser($Indexer, $data['query']);
    $data['highlight'] = $q['highlight'];

    if (empty($q['parsed_ary'])) return array();

    // lookup all words found in the query
    $lookup = $Indexer->lookup($q['words']);

    // get all pages in this dokuwiki site (!: includes nonexistent pages)
    $pages_all = array();
    foreach ($Indexer->getPages() as $id) {
        $pages_all[$id] = 0; // base: 0 hit
    }

    // process the query
    $stack = array();
    foreach ($q['parsed_ary'] as $token) {
        switch (substr($token, 0, 3)) {
            case 'W+:':
            case 'W-:':
            case 'W_:': // word
                $word    = substr($token, 3);
                $stack[] = (array) $lookup[$word];
                break;
            case 'P+:':
            case 'P-:': // phrase
                $phrase = substr($token, 3);
                // since phrases are always parsed as ((W1)(W2)...(P)),
                // the end($stack) always points the pages that contain
                // all words in this phrase
                $pages  = end($stack);
                $pages_matched = array();
                foreach(array_keys($pages) as $id){
                    $evdata = array(
                        'id' => $id,
                        'phrase' => $phrase,
                        'text' => rawWiki($id)
                    );
                    $evt = new Doku_Event('FULLTEXT_PHRASE_MATCH',$evdata);
                    if ($evt->advise_before() && $evt->result !== true) {
                        $text = utf8_strtolower($evdata['text']);
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
                $ns = substr($token, 3);
                $pages_matched = array();
                foreach (array_keys($pages_all) as $id) {
                    if (strpos($id, $ns) === 0) {
                        $pages_matched[$id] = 0; // namespace: always 0 hit
                    }
                }
                $stack[] = $pages_matched;
                break;
            case 'AND': // and operation
                list($pages1, $pages2) = array_splice($stack, -2);
                $stack[] = ft_resultCombine(array($pages1, $pages2));
                break;
            case 'OR':  // or operation
                list($pages1, $pages2) = array_splice($stack, -2);
                $stack[] = ft_resultUnite(array($pages1, $pages2));
                break;
            case 'NOT': // not operation (unary)
                $pages   = array_pop($stack);
                $stack[] = ft_resultComplement(array($pages_all, $pages));
                break;
        }
    }
    $docs = array_pop($stack);

    if (empty($docs)) return array();

    // check: settings, acls, existence
    foreach (array_keys($docs) as $id) {
        if (isHiddenPage($id) || auth_quickaclcheck($id) < AUTH_READ || !page_exists($id, '', false)) {
            unset($docs[$id]);
        }
    }

    // sort docs by count
    arsort($docs);

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
function ft_backlinks($id, $ignore_perms = false){
    $result = idx_get_indexer()->lookupKey('relation_references', $id);

    if(!count($result)) return $result;

    // check ACL permissions
    foreach(array_keys($result) as $idx){
        if(($ignore_perms !== true && (
                isHiddenPage($result[$idx]) || auth_quickaclcheck($result[$idx]) < AUTH_READ
            )) || !page_exists($result[$idx], '', false)){
            unset($result[$idx]);
        }
    }

    sort($result);
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
function ft_mediause($id, $ignore_perms = false){
    $result = idx_get_indexer()->lookupKey('relation_media', $id);

    if(!count($result)) return $result;

    // check ACL permissions
    foreach(array_keys($result) as $idx){
        if(($ignore_perms !== true && (
                    isHiddenPage($result[$idx]) || auth_quickaclcheck($result[$idx]) < AUTH_READ
                )) || !page_exists($result[$idx], '', false)){
            unset($result[$idx]);
        }
    }

    sort($result);
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
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Adrian Lang <lang@cosmocode.de>
 */
function ft_pageLookup($id, $in_ns=false, $in_title=false){
    $data = compact('id', 'in_ns', 'in_title');
    $data['has_titles'] = true; // for plugin backward compatibility check
    return trigger_event('SEARCH_QUERY_PAGELOOKUP', $data, '_ft_pageLookup');
}

function _ft_pageLookup(&$data){
    // split out original parameters
    $id = $data['id'];
    if (preg_match('/(?:^| )@(\w+)/', $id, $matches)) {
        $ns = cleanID($matches[1]) . ':';
        $id = str_replace($matches[0], '', $id);
    }

    $in_ns    = $data['in_ns'];
    $in_title = $data['in_title'];
    $cleaned = cleanID($id);

    $Indexer = idx_get_indexer();
    $page_idx = $Indexer->getPages();

    $pages = array();
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

    // discard hidden pages
    // discard nonexistent pages
    // check ACL permissions
    foreach(array_keys($pages) as $idx){
        if(!isVisiblePage($idx) || !page_exists($idx) ||
           auth_quickaclcheck($idx) < AUTH_READ) {
            unset($pages[$idx]);
        }
    }

    uksort($pages,'ft_pagesorter');
    return $pages;
}

/**
 * Tiny helper function for comparing the searched title with the title
 * from the search index. This function is a wrapper around stripos with
 * adapted argument order and return value.
 */
function _ft_pageLookupTitleCompare($search, $title) {
    return stripos($title, $search) !== false;
}

/**
 * Sort pages based on their namespace level first, then on their string
 * values. This makes higher hierarchy pages rank higher than lower hierarchy
 * pages.
 */
function ft_pagesorter($a, $b){
    $ac = count(explode(':',$a));
    $bc = count(explode(':',$b));
    if($ac < $bc){
        return -1;
    }elseif($ac > $bc){
        return 1;
    }
    return strcmp ($a,$b);
}

/**
 * Creates a snippet extract
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @triggers FULLTEXT_SNIPPET_CREATE
 */
function ft_snippet($id,$highlight){
    $text = rawWiki($id);
    $text = str_replace("\xC2\xAD",'',$text); // remove soft-hyphens
    $evdata = array(
            'id'        => $id,
            'text'      => &$text,
            'highlight' => &$highlight,
            'snippet'   => '',
            );

    $evt = new Doku_Event('FULLTEXT_SNIPPET_CREATE',$evdata);
    if ($evt->advise_before()) {
        $match = array();
        $snippets = array();
        $utf8_offset = $offset = $end = 0;
        $len = utf8_strlen($text);

        // build a regexp from the phrases to highlight
        $re1 = '('.join('|',array_map('ft_snippet_re_preprocess', array_map('preg_quote_cb',array_filter((array) $highlight)))).')';
        $re2 = "$re1.{0,75}(?!\\1)$re1";
        $re3 = "$re1.{0,45}(?!\\1)$re1.{0,45}(?!\\1)(?!\\2)$re1";

        for ($cnt=4; $cnt--;) {
            if (0) {
            } else if (preg_match('/'.$re3.'/iu',$text,$match,PREG_OFFSET_CAPTURE,$offset)) {
            } else if (preg_match('/'.$re2.'/iu',$text,$match,PREG_OFFSET_CAPTURE,$offset)) {
            } else if (preg_match('/'.$re1.'/iu',$text,$match,PREG_OFFSET_CAPTURE,$offset)) {
            } else {
                break;
            }

            list($str,$idx) = $match[0];

            // convert $idx (a byte offset) into a utf8 character offset
            $utf8_idx = utf8_strlen(substr($text,0,$idx));
            $utf8_len = utf8_strlen($str);

            // establish context, 100 bytes surrounding the match string
            // first look to see if we can go 100 either side,
            // then drop to 50 adding any excess if the other side can't go to 50,
            $pre = min($utf8_idx-$utf8_offset,100);
            $post = min($len-$utf8_idx-$utf8_len,100);

            if ($pre>50 && $post>50) {
                $pre = $post = 50;
            } else if ($pre>50) {
                $pre = min($pre,100-$post);
            } else if ($post>50) {
                $post = min($post, 100-$pre);
            } else if ($offset == 0) {
                // both are less than 50, means the context is the whole string
                // make it so and break out of this loop - there is no need for the
                // complex snippet calculations
                $snippets = array($text);
                break;
            }

            // establish context start and end points, try to append to previous
            // context if possible
            $start = $utf8_idx - $pre;
            $append = ($start < $end) ? $end : false;  // still the end of the previous context snippet
            $end = $utf8_idx + $utf8_len + $post;      // now set it to the end of this context

            if ($append) {
                $snippets[count($snippets)-1] .= utf8_substr($text,$append,$end-$append);
            } else {
                $snippets[] = utf8_substr($text,$start,$end-$start);
            }

            // set $offset for next match attempt
            // continue matching after the current match
            // if the current match is not the longest possible match starting at the current offset
            // this prevents further matching of this snippet but for possible matches of length
            // smaller than match length + context (at least 50 characters) this match is part of the context
            $utf8_offset = $utf8_idx + $utf8_len;
            $offset = $idx + strlen(utf8_substr($text,$utf8_idx,$utf8_len));
            $offset = utf8_correctIdx($text,$offset);
        }

        $m = "\1";
        $snippets = preg_replace('/'.$re1.'/iu',$m.'$1'.$m,$snippets);
        $snippet = preg_replace('/'.$m.'([^'.$m.']*?)'.$m.'/iu','<strong class="search_hit">$1</strong>',hsc(join('... ',$snippets)));

        $evdata['snippet'] = $snippet;
    }
    $evt->advise_after();
    unset($evt);

    return $evdata['snippet'];
}

/**
 * Wraps a search term in regex boundary checks.
 */
function ft_snippet_re_preprocess($term) {
    // do not process asian terms where word boundaries are not explicit
    if(preg_match('/'.IDX_ASIAN.'/u',$term)){
        return $term;
    }

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

    if(substr($term,0,2) == '\\*'){
        $term = substr($term,2);
    }else{
        $term = $BL.$term;
    }

    if(substr($term,-2,2) == '\\*'){
        $term = substr($term,0,-2);
    }else{
        $term = $term.$BR;
    }

    if($term == $BL || $term == $BR || $term == $BL.$BR) $term = '';
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
 */
function ft_resultCombine($args){
    $array_count = count($args);
    if($array_count == 1){
        return $args[0];
    }

    $result = array();
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
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 */
function ft_resultUnite($args) {
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
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 */
function ft_resultComplement($args) {
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
 */
function ft_queryParser($Indexer, $query){
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
    $terms = preg_split('/(-?".*?")/u', utf8_strtolower($query), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    foreach ($terms as $term) {
        $parsed = '';
        if (preg_match('/^(-?)"(.+)"$/u', $term, $matches)) {
            // phrase-include and phrase-exclude
            $not = $matches[1] ? 'NOT' : '';
            $parsed = $not.ft_termParser($Indexer, $matches[2], false, true);
        } else {
            // fix incomplete phrase
            $term = str_replace('"', ' ', $term);

            // fix parentheses
            $term = str_replace(')'  , ' ) ', $term);
            $term = str_replace('('  , ' ( ', $term);
            $term = str_replace('- (', ' -(', $term);

            // treat pipe symbols as 'OR' operators
            $term = str_replace('|', ' or ', $term);

            // treat ideographic spaces (U+3000) as search term separators
            // FIXME: some more separators?
            $term = preg_replace('/[ \x{3000}]+/u', ' ',  $term);
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
                    $parsed .= 'NOT(N+:'.$matches[1].')';
                } elseif (preg_match('/^(?:@|ns:)(.+)$/u', $token, $matches)) {
                    // namespace-include
                    $parsed .= '(N+:'.$matches[1].')';
                } elseif (preg_match('/^-(.+)$/', $token, $matches)) {
                    // word-exclude
                    $parsed .= 'NOT('.ft_termParser($Indexer, $matches[1]).')';
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
    $parsed_query = preg_replace('/(NOT|OR)+\)/u', ')'      , $parsed_query);
    $parsed_query = preg_replace('/(OR)+/u'      , 'OR'     , $parsed_query);
    $parsed_query = preg_replace('/\(OR/u'       , '('      , $parsed_query);
    $parsed_query = preg_replace('/^OR|OR$/u'    , ''       , $parsed_query);
    $parsed_query = preg_replace('/\)(NOT)?\(/u' , ')AND$1(', $parsed_query);

    // adjustment: make highlightings right
    $parens_level     = 0;
    $notgrp_levels    = array();
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
    $parsed_ary     = array();
    $ope_stack      = array();
    $ope_precedence = array(')' => 1, 'OR' => 2, 'AND' => 3, 'NOT' => 4, '(' => 5);
    $ope_regex      = '/([()]|OR|AND|NOT)/u';

    $tokens = preg_split($ope_regex, $parsed_query, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $token) {
        if (preg_match($ope_regex, $token)) {
            // operator
            $last_ope = end($ope_stack);
            while ($ope_precedence[$token] <= $ope_precedence[$last_ope] && $last_ope != '(') {
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
            $token_decoded = str_replace(array('OP', 'CP'), array('(', ')'), $token);
            $parsed_ary[] = $token_decoded;
        }
    }
    $parsed_ary = array_values(array_merge($parsed_ary, array_reverse($ope_stack)));

    // cleanup: each double "NOT" in RPN array actually does nothing
    $parsed_ary_count = count($parsed_ary);
    for ($i = 1; $i < $parsed_ary_count; ++$i) {
        if ($parsed_ary[$i] === 'NOT' && $parsed_ary[$i - 1] === 'NOT') {
            unset($parsed_ary[$i], $parsed_ary[$i - 1]);
        }
    }
    $parsed_ary = array_values($parsed_ary);

    // build return value
    $q = array();
    $q['query']      = $query;
    $q['parsed_str'] = $parsed_query;
    $q['parsed_ary'] = $parsed_ary;

    foreach ($q['parsed_ary'] as $token) {
        if ($token[2] !== ':') continue;
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
    foreach (array('words', 'phrases', 'highlight', 'ns', 'notns', 'and', 'not') as $key) {
        $q[$key] = empty($q[$key]) ? array() : array_values(array_unique($q[$key]));
    }

    return $q;
}

/**
 * Transforms given search term into intermediate representation
 *
 * This function is used in ft_queryParser() and not for general purpose use.
 *
 * @author Kazutaka Miyasaka <kazmiya@gmail.com>
 */
function ft_termParser($Indexer, $term, $consider_asian = true, $phrase_mode = false) {
    $parsed = '';
    if ($consider_asian) {
        // successive asian characters need to be searched as a phrase
        $words = preg_split('/('.IDX_ASIAN.'+)/u', $term, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            $phrase_mode = $phrase_mode ? true : preg_match('/'.IDX_ASIAN.'/u', $word);
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

//Setup VIM: ex: et ts=4 :
