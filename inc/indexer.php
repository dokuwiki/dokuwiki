<?php
/**
 * Common DokuWiki functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_CONF.'dokuwiki.php');
  require_once(DOKU_INC.'inc/io.php');
  require_once(DOKU_INC.'inc/utf8.php');
  require_once(DOKU_INC.'inc/parserutils.php');

/**
 * based upon class.search_indexer_phpcms.php::index_entry
 */
function idx_getPageWords($id){
    $body  = rawWiki($id);
    $body  = utf8_stripspecials($body,' ','._\-:');
    $body  = utf8_strtolower($body);
    $body  = trim($body);
    $words = explode(' ',$body);
    sort($words);

    $index = array(); //resulting index
    $old   = '';
    $doit  = true;
    $pos   = 0;

    //compact wordlist FIXME check for stopwords

    foreach($words as $word){
        if(strlen($word) == 0) continue;

        // it's the same word
        if($word == $old){
            if($doit == false) {
                // we didn't wanted it last time
                continue;
            }
            // just increase the counter
            $index[$word]++;
            continue;
        }

        // rememember old word
        $old  = $word;
        $doit = true;

        // checking minimum word-size (excepting numbers)
        if(!is_numeric($word)) {
            if(strlen($word) < 3) {  #FIXME add config option for max wordsize
                $doit = false;
                continue;
            }
        }
      
        //FIXME add stopword check

        // add to index
        $index[$word] = 1;
    }

    return $index;
}



//Setup VIM: ex: et ts=4 enc=utf-8 :
