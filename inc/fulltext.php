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
 */
function ft_pageSearch($query){
    $q = ft_queryParser($query);

    // lookup all words found in the query
    $words  = array_merge($q['and'],$q['not']);
    foreach($q['phrases'] as $phrase){
        $words  = array_merge($words,$phrase['words']);
    }
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


    // combine and words
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
            if(!preg_match_all('/'.$regex.'/usi',$text)){
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


