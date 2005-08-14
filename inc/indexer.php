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
 * Split a page into words
 *
 * It is based upon PHPCMS's indexer function index_entry
 *
 * Returns an array of of word counts, false if an error occured
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_getPageWords($page){
    global $conf;
    $word_idx = file($conf['cachedir'].'/word.idx');
    $swfile   = DOKU_INC.'inc/lang/'.$conf['lang'].'/stopwords.txt';
    if(@file_exists($swfile)){
        $stopwords = file($swfile);
    }else{
        $stopwords = array();
    }

    // split page into words
    $body  = rawWiki($page);
    $body  = utf8_stripspecials($body,' ','._\-:');
    $body  = utf8_strtolower($body);
    $body  = trim($body);
    $words = explode(' ',$body);
    sort($words);

    $index = array(); //resulting index
    $old   = '';
    $wid   = -1;
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
            $index[$wid]++;
            continue;
        }

        // rememember old word
        $old  = $word;
        $doit = true;

        // checking minimum word-size (excepting numbers)
        if(!is_numeric($word)) {
            if(strlen($word) < 3) {
                $doit = false;
                continue;
            }
        }
      
        // stopword check
        if(is_int(array_search("$word\n",$stopwords))){
            $doit = false;
            continue;
        }

        // get word ID
        $wid = array_search("$word\n",$word_idx);
        if(!is_int($wid)){
            $word_idx[] = "$word\n";
            $wid = count($word_idx)-1;
        }
        // add to index
        $index[$wid] = 1;
    }

    // save back word index
    $fh = fopen($conf['cachedir'].'/word.idx','w');
    if(!$fh){
        trigger_error("Failed to write word.idx", E_USER_ERROR);
        return false;
    }
    fwrite($fh,join('',$word_idx));
    fclose($fh);

    return $index;
}

/**
 * Adds/updates the search for the given page
 *
 * This is the core function of the indexer which does most
 * of the work. This function needs to be called with proper
 * locking!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_addPage($page){
    global $conf;

    // load known words and documents
    $page_idx = file($conf['cachedir'].'/page.idx');

    // get page id (this is the linenumber in page.idx)
    $pid = array_search("$page\n",$page_idx);
    if(!is_int($pid)){
        $page_idx[] = "$page\n";
        $pid = count($page_idx)-1;
        // page was new - write back
        $fh = fopen($conf['cachedir'].'/page.idx','w');
        if(!$fh) return false;
        fwrite($fh,join('',$page_idx));
        fclose($fh);
    }

    // get word usage in page
    $words = idx_getPageWords($page);
    if($words === false) return false;
    if(!count($words)) return true;

    // Open index and temp file
    $idx = fopen($conf['cachedir'].'/index.idx','r');
    $tmp = fopen($conf['cachedir'].'/index.tmp','w');
    if(!$idx || !$tmp){
       trigger_error("Failed to open index files", E_USER_ERROR);
       return false;
    } 

    // copy from index to temp file, modifying were needed
    $lno = 0;
    $line = '';
    while (!feof($idx)) {
        // read full line
        $line .= fgets($idx, 4096);
        if(substr($line,-1) != "\n") continue;

        // write a new Line to temp file
        idx_writeIndexLine($tmp,$line,$pid,$words[$lno]);

        $line = ''; // reset line buffer
        $lno++;     // increase linecounter
    }
    fclose($idx);

    // add missing lines (usually index and word should contain
    // the same number of lines, however if the page contained
    // new words the word file has some more lines which need to
    // be added here
    $word_idx = file($conf['cachedir'].'/word.idx');
    $wcnt = count($word_idx);
    for($lno; $lno<$wcnt; $lno++){
        idx_writeIndexLine($tmp,'',$pid,$words[$lno]);
    }

    // close the temp file and move it over to be the new one
    fclose($tmp);
    return rename($conf['cachedir'].'/index.tmp',
                  $conf['cachedir'].'/index.idx');
}

/**
 * Write a new index line to the filehandle
 *
 * This function writes an line for the index file to the
 * given filehandle. It removes the given document from
 * the given line and readds it when $count is >0.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_writeIndexLine($fh,$line,$pid,$count){
    $line = trim($line);

    if($line != ''){
        $parts = explode(':',$line);
        // remove doc from given line
        foreach($parts as $part){
            if($part == '') continue;
            list($doc,$cnt) = explode('*',$part);
            if($doc != $pid){
                fwrite($fh,"$doc*$cnt:");
            }
        }
    }

    // add doc
    if ($count){
        fwrite($fh,"$pid*$count");
    }

    // add newline
    fwrite($fh,"\n");
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
