<?php
/**
 * DokuWiki fulltextsearch functions using the index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/indexer.php');


/**
 * The fulltext search
 *
 * Returns a list of matching documents for the given query
 *
 */
function ft_pageSearch($query,&$poswords){
    $q = ft_queryParser($query);

    // use this for higlighting later:
    $poswords = join(' ',$q['and']);

    // lookup all words found in the query
    $words  = array_merge($q['and'],$q['not']);
    if(!count($words)) return array();
    $result = idx_lookup($words);

    // merge search results with query
    foreach($q['and'] as $pos => $w){
        $q['and'][$pos] = $result[$w];
    }
    // create a list of unwanted docs
    $not = array();
    foreach($q['not'] as $pos => $w){
        $not = array_merge($not,array_keys($result[$w]));
    }

    // combine and-words
    if(count($q['and']) > 1){
        $docs = ft_resultCombine($q['and']);
    }else{
        $docs = $q['and'][0];
    }
    if(!count($docs)) return array();

    // remove negative matches
    foreach($not as $n){
        unset($docs[$n]);
    }

    if(!count($docs)) return array();

    // handle phrases
    if(count($q['phrases'])){
        //build a regexp
        $q['phrases'] = array_map('utf8_strtolower',$q['phrases']);
        $q['phrases'] = array_map('preg_quote',$q['phrases']);
        $regex = '('.join('|',$q['phrases']).')';

        // check the source of all documents for the exact phrases
        foreach(array_keys($docs) as $id){
            $text  = utf8_strtolower(rawWiki($id));
            if(!preg_match('/'.$regex.'/usi',$text)){
                unset($docs[$id]); // no hit - remove
            }
        }
    }

    if(!count($docs)) return array();

    // if there are any hits left, sort them by count
    arsort($docs);

    return $docs;
}

/**
 * Quicksearch for pagenames
 *
 * By default it only matches the pagename and ignores the
 * namespace. This can be changed with the second parameter
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ft_pageLookup($id,$pageonly=true){
    global $conf;
    $id    = preg_quote($id,'/');
    $pages = file($conf['cachedir'].'/page.idx');
    $pages = array_values(preg_grep('/'.$id.'/',$pages));

    $cnt = count($pages);
    for($i=0; $i<$cnt; $i++){
        if($pageonly){
            if(!preg_match('/'.$id.'/',noNS($pages[$i]))){
                unset($pages[$i]);
                continue;
            } 
        }
        if(!@file_exists(wikiFN($pages[$i]))){
            unset($pages[$i]);
            continue;
        }
    }
    sort($pages);
    return $pages;
}

/**
 * Creates a snippet extract
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ft_snippet($id,$poswords){
    $poswords = preg_quote($poswords,'#');
    $re       = '('.str_replace(' ','|',$poswords).')';
    $text     = rawWiki($id);
    //FIXME caseinsensitive matching doesn't work with UTF-8!?
    preg_match_all('#(.{0,50})'.$re.'(.{0,50})#iu',$text,$matches,PREG_SET_ORDER);

    $cnt = 0;
    $snippet = '';
    foreach($matches as $match){
        $snippet .= '...'.htmlspecialchars($match[1]);
        $snippet .= '<span class="search_hit">';
        $snippet .= htmlspecialchars($match[2]);
        $snippet .= '</span>';
        $snippet .= htmlspecialchars($match[3]).'... ';
        if($cnt++ == 2) break;
    }

    return $snippet;
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
    $result = array();
    foreach ($args[0] as $key1 => $value1) {
        for ($i = 1; $i !== $array_count; $i++) {
            foreach ($args[$i] as $key2 => $value2) {
                if ((string) $key1 === (string) $key2) {
                    if(!isset($result[$key1])) $result[$key1] = $value1;
                    $result[$key1] += $value2;
                }
            }
        }
    }
    return $result;
}

/**
 * Builds an array of search words from a query
 *
 * @todo support OR and parenthesises?
 */
function ft_queryParser($query){
    global $conf;
    $swfile   = DOKU_INC.'inc/lang/'.$conf['lang'].'/stopwords.txt';
    if(@file_exists($swfile)){
        $stopwords = file($swfile);
    }else{
        $stopwords = array();
    }

    $q = array();
    $q['query']   = $query;
    $q['phrases'] = array();
    $q['and']     = array();
    $q['not']     = array();
    
    // handle phrase searches
    while(preg_match('/"(.*?)"/',$query,$match)){
        $q['phrases'][] = $match[0];
        $q['and'] = array_merge(idx_tokenizer($match[0],$stopwords));
        $query = preg_replace('/"(.*?)"/','',$query,1);
    }

    $words = explode(' ',$query);
    foreach($words as $w){
        if($w{0} == '-'){
            $token = idx_tokenizer($w,$stopwords);
            if(count($token)) $q['not'] = array_merge($q['not'],$token);
        }else{
            $token = idx_tokenizer($w,$stopwords);
            if(count($token)) $q['and'] = array_merge($q['and'],$token);
        }
    }

    return $q;
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
