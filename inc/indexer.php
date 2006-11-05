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

// Asian characters are handled as words. The following regexp defines the
// Unicode-Ranges for Asian characters
// Ranges taken from http://en.wikipedia.org/wiki/Unicode_block
// I'm no language expert. If you think some ranges are wrongly chosen or
// a range is missing, please contact me
define('IDX_ASIAN','['.
                   '\x{0E00}-\x{0E7F}'.  // Thai
                   '\x{2E80}-\x{D7AF}'.  // CJK -> Hangul
                   '\x{F900}-\x{FAFF}'.  // CJK Compatibility Ideographs
                   '\x{FE30}-\x{FE4F}'.  // CJK Compatibility Forms
                   ']');


/**
 * Split a page into words
 *
 * Returns an array of of word counts, false if an error occured
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Christopher Smith <chris@jalakai.co.uk>
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

    $body   = rawWiki($page);
    $body   = strtr($body, "\r\n\t", '   ');
    $tokens = explode(' ', $body);
    $tokens = array_count_values($tokens);   // count the frequency of each token

// ensure the deaccented or romanised page names of internal links are added to the token array
// (this is necessary for the backlink function -- there maybe a better way!)
    if ($conf['deaccent']) {
      $links = p_get_metadata($page,'relation references');

      $tmp = join(' ',array_keys($links));                // make a single string
      $tmp = strtr($tmp, ':', ' ');                       // replace namespace separator with a space
      $link_tokens = array_unique(explode(' ', $tmp));    // break into tokens

      foreach ($link_tokens as $link_token) {
        if (isset($tokens[$link_token])) continue;
        $tokens[$link_token] = 1;
      }
    }

    $words = array();
    foreach ($tokens as $word => $count) {
        // simple filter to restrict use of utf8_stripspecials
        if (preg_match('/[^0-9A-Za-z]/u', $word)) {
            // handle asian chars as single words (may fail on older PHP version)
            $asia = @preg_replace('/('.IDX_ASIAN.')/u','\1 ',$word);
            if(!is_null($asia)) $word = $asia; //recover from regexp failure
            $arr = explode(' ', utf8_stripspecials($word,' ','._\-:\*'));
            $arr = array_count_values($arr);

            foreach ($arr as $w => $c) {
                if (!is_numeric($w) && strlen($w) < 3) continue;
                $w = utf8_strtolower($w);
                $words[$w] = $c * $count + (isset($words[$w]) ? $words[$w] : 0);
            }
        } else {
            if (!is_numeric($word) && strlen($word) < 3) continue;
            $word = strtolower($word);
            $words[$word] = $count + (isset($words[$word]) ? $words[$word] : 0);
        }
    }

    // arrive here with $words = array(word => frequency)

    $index = array(); //resulting index
    foreach ($words as $word => $freq) {
    if (is_int(array_search("$word\n",$stopwords))) continue;
        $wid = array_search("$word\n",$word_idx);
        if(!is_int($wid)){
            $word_idx[] = "$word\n";
            $wid = count($word_idx)-1;
        }
        $index[$wid] = $freq;
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

    // load known documents
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
    // try rename first (fast) fallback to copy (slow)
    io_rename($conf['cachedir'].'/index.tmp',
              $conf['cachedir'].'/index.idx');
    return false;
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

/**
 * Lookup words in index
 *
 * Takes an array of word and will return a list of matching
 * documents for each one.
 *
 * Important: No ACL checking is done here! All results are
 *            returned, regardless of permissions
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_lookup($words){
    global $conf;

    $result = array();

    // load known words and documents
    $page_idx = file($conf['cachedir'].'/page.idx');
    $word_idx = file($conf['cachedir'].'/word.idx');

    // get word IDs
    $wids = array();
    foreach($words as $word){
        $result[$word] = array();
        $wild = 0;
        $xword = $word;

        // check for wildcards
        if(substr($xword,0,1) == '*'){
            $xword = substr($xword,1);
            $wild  = 1;
            $ptn = '/'.preg_quote($xword,'/').'$/';
#            $l = -1*strlen($xword)-1;
        }
        if(substr($xword,-1,1) == '*'){
            $xword = substr($xword,0,-1);
            $wild += 2;
        }

        // look for the ID(s) for the given word
        if($wild){  // handle wildcard search
            $cnt = count($word_idx);
            for($wid=0; $wid<$cnt; $wid++){
                $iword = $word_idx[$wid];
                if( (($wild==3) && is_int(strpos($iword,$xword))) ||
#                    (($wild==1) && ("$xword\n" == substr($iword,$l))) ||
                    (($wild==1) && preg_match($ptn,$iword)) ||
#                    (($wild==2) && ($xword == substr($iword,0,strlen($xword))))
                    (($wild==2) && (0 === strpos($iword,$xword)))

                  ){
                    $wids[] = $wid;
                    $result[$word][] = $wid;
                }
            }
        }else{     // handle exact search
            $wid = array_search("$word\n",$word_idx);
            if(is_int($wid)){
                $wids[] = $wid;
                $result[$word][] = $wid;
            }else{
                $result[$word] = array();
            }
        }
    }
    sort($wids);
    $wids = array_unique($wids);

    // Open index
    $idx = fopen($conf['cachedir'].'/index.idx','r');
    if(!$idx){
       msg("Failed to open index file",-1);
       return false;
    }

    // Walk the index til the lines are found
    $docs = array();                          // hold docs found
    $lno  = 0;
    $line = '';
    $srch = array_shift($wids);               // which word do we look for?
    while (!feof($idx)) {
        // read full line
        $line .= fgets($idx, 4096);
        if(substr($line,-1) != "\n") continue;
        if($lno > $srch)             break;   // shouldn't happen


        // do we want this line?
        if($lno == $srch){
            // add docs to list
            $docs[$srch] = idx_parseIndexLine($page_idx,$line);

            $srch = array_shift($wids);        // next word to look up
            if($srch == null) break;           // no more words
        }

        $line = ''; // reset line buffer
        $lno++;     // increase linecounter
    }
    fclose($idx);


    // merge found pages into final result array
    $final = array();
    foreach(array_keys($result) as $word){
        $final[$word] = array();
        foreach($result[$word] as $wid){
            $hits = &$docs[$wid];
            foreach ($hits as $hitkey => $hitcnt) {
                $final[$word][$hitkey] = $hitcnt + $final[$word][$hitkey];
            }
        }
    }
    return $final;
}

/**
 * Returns a list of documents and counts from a index line
 *
 * It omits docs with a count of 0 and pages that no longer
 * exist.
 *
 * @param  array  $page_idx The list of known pages
 * @param  string $line     A line from the main index
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_parseIndexLine(&$page_idx,$line){
    $result = array();

    $line = trim($line);
    if($line == '') return $result;

    $parts = explode(':',$line);
    foreach($parts as $part){
        if($part == '') continue;
        list($doc,$cnt) = explode('*',$part);
        if(!$cnt) continue;
        $doc = trim($page_idx[$doc]);
        if(!$doc) continue;
        // make sure the document still exists
        if(!@file_exists(wikiFN($doc,'',false))) continue;

        $result[$doc] = $cnt;
    }
    return $result;
}

/**
 * Tokenizes a string into an array of search words
 *
 * Uses the same algorithm as idx_getPageWords()
 *
 * @param string   $string     the query as given by the user
 * @param arrayref $stopwords  array of stopwords
 * @param boolean  $wc         are wildcards allowed?
 *
 * @todo make combined function to use alone or in getPageWords
 */
function idx_tokenizer($string,&$stopwords,$wc=false){
    $words = array();
    $wc = ($wc) ? '' : $wc = '\*';

    if(preg_match('/[^0-9A-Za-z]/u', $string)){
        // handle asian chars as single words (may fail on older PHP version)
        $asia = @preg_replace('/('.IDX_ASIAN.')/u','\1 ',$string);
        if(!is_null($asia)) $string = $asia; //recover from regexp failure

        $arr = explode(' ', utf8_stripspecials($string,' ','\._\-:'.$wc));
        foreach ($arr as $w) {
            if (!is_numeric($w) && strlen($w) < 3) continue;
            $w = utf8_strtolower($w);
            if($stopwords && is_int(array_search("$w\n",$stopwords))) continue;
            $words[] = $w;
        }
    }else{
        $w = $string;
        if (!is_numeric($w) && strlen($w) < 3) return $words;
        $w = strtolower($w);
        if(is_int(array_search("$w\n",$stopwords))) return $words;
        $words[] = $w;
    }

    return $words;
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
