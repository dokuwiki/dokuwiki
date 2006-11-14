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
 * Write a list of strings to an index file.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_saveIndex($pre, $wlen, $idx){
    global $conf;
    $fn = $conf['indexdir'].'/'.$pre.$wlen;
    $fh = @fopen($fn.'.tmp','w');
    if(!$fh) return false;
    fwrite($fh,join('',$idx));
    fclose($fh);
    if($conf['fperm']) chmod($fn.'.tmp', $conf['fperm']);
    io_rename($fn.'.tmp', $fn.'.idx');
    return true;
}

/**
 * Read the list of words in an index (if it exists).
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_getIndex($pre, $wlen){
    global $conf;
    $fn = $conf['indexdir'].'/'.$pre.$wlen.'.idx';
    if(!@file_exists($fn)) return array();
    return file($fn);
}

/**
 * Create an empty index file if it doesn't exist yet.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_touchIndex($pre, $wlen){
    global $conf;
    $fn = $conf['indexdir'].'/'.$pre.$wlen.'.idx';
    if(!@file_exists($fn)){
        touch($fn);
        if($conf['fperm']) chmod($fn, $conf['fperm']);
    }
}

/**
 * Split a page into words
 *
 * Returns an array of word counts, false if an error occured.
 * Array is keyed on the word length, then the word index.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Christopher Smith <chris@jalakai.co.uk>
 */
function idx_getPageWords($page){
    global $conf;
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

      if (!empty($links)) {
        $tmp = join(' ',array_keys($links));                // make a single string
        $tmp = strtr($tmp, ':', ' ');                       // replace namespace separator with a space
        $link_tokens = array_unique(explode(' ', $tmp));    // break into tokens

        foreach ($link_tokens as $link_token) {
          if (isset($tokens[$link_token])) continue;
          $tokens[$link_token] = 1;
        }
      }
    }

    $words = array();
    foreach ($tokens as $word => $count) {
        $arr = idx_tokenizer($word,$stopwords);
        $arr = array_count_values($arr);
        foreach ($arr as $w => $c) {
            $l = strlen($w);
            if(isset($words[$l])){
                $words[$l][$w] = $c * $count + (isset($words[$l][$w]) ? $words[$l][$w] : 0);
            }else{
                $words[$l] = array($w => $c * $count);
            }
        }
    }

    // arrive here with $words = array(wordlen => array(word => frequency))

    $index = array(); //resulting index
    foreach (array_keys($words) as $wlen){
        $word_idx = idx_getIndex('w',$wlen);
        foreach ($words[$wlen] as $word => $freq) {
            $wid = array_search("$word\n",$word_idx);
            if(!is_int($wid)){
                $word_idx[] = "$word\n";
                $wid = count($word_idx)-1;
            }
            if(!isset($index[$wlen]))
                $index[$wlen] = array();
            $index[$wlen][$wid] = $freq;
        }

        // save back word index
        if(!idx_saveIndex('w',$wlen,$word_idx)){
            trigger_error("Failed to write word index", E_USER_ERROR);
            return false;
        }
    }

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
    $page_idx = idx_getIndex('page','');

    // get page id (this is the linenumber in page.idx)
    $pid = array_search("$page\n",$page_idx);
    if(!is_int($pid)){
        $page_idx[] = "$page\n";
        $pid = count($page_idx)-1;
        // page was new - write back
        if (!idx_saveIndex('page','',$page_idx))
            return false;
    }

    // get word usage in page
    $words = idx_getPageWords($page);
    if($words === false) return false;
    if(!count($words)) return true;

    foreach(array_keys($words) as $wlen){
        // Open index and temp file
        $fn = $conf['indexdir']."/i$wlen";
        idx_touchIndex('i',$wlen);
        $idx = fopen($fn.'.idx','r');
        $tmp = fopen($fn.'.tmp','w');
        if(!$idx || !$tmp){
            trigger_error("Failed to open index files", E_USER_ERROR);
            return false;
        }

        // copy from index to temp file, modifying where needed
        $lno = 0;
        $line = '';
        while (!feof($idx)) {
            // read full line
            $line .= fgets($idx, 4096);
            if(substr($line,-1) != "\n") continue;

            // write a new Line to temp file
            idx_writeIndexLine($tmp,$line,$pid,$words[$wlen][$lno]);

            $line = ''; // reset line buffer
            $lno++;     // increase linecounter
        }
        fclose($idx);

        // add missing lines (usually index and word should contain
        // the same number of lines, however if the page contained
        // new words the word file has some more lines which need to
        // be added here
        $word_idx = idx_getIndex('w',$wlen);
        $wcnt = count($word_idx);
        for($lno; $lno<$wcnt; $lno++){
            idx_writeIndexLine($tmp,'',$pid,$words[$wlen][$lno]);
        }

        // close the temp file and move it over to be the new one
        fclose($tmp);
        if($conf['fperm']) chmod($fn.'.tmp', $conf['fperm']);
        // try rename first (fast) fallback to copy (slow)
        io_rename($fn.'.tmp', $fn.'.idx');
    }

    return true;
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
 * Get the word lengths that have been indexed.
 *
 * Reads the index directory and returns an array of lengths
 * that there are indices for.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_indexLengths($minlen){
    global $conf;
    $dir = @opendir($conf['indexdir']);
    if($dir===false)
        return array();
    $idx = array();
    // Exact match first.
    if(@file_exists($conf['indexdir']."/i$minlen.idx"))
        $idx[] = $minlen;
    while (($f = readdir($dir)) !== false) {
        if (substr($f,0,1) == 'i' && substr($f,-4) == '.idx'){
            $i = substr($f,1,-4);
            if (is_numeric($i) && $i > $minlen)
                $idx[] = $i;
        }
    }
    closedir($dir);
    return $idx;
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
    $page_idx = idx_getIndex('page','');

    // get word IDs
    $wids = array();
    foreach($words as $word){
        $result[$word] = array();
        $wild = 0;
        $xword = $word;
        $wlen = strlen($word);

        // check for wildcards
        if(substr($xword,0,1) == '*'){
            $xword = substr($xword,1);
            $wild  = 1;
            $ptn = '/'.preg_quote($xword,'/').'$/';
            $wlen -= 1;
#            $l = -1*strlen($xword)-1;
        }
        if(substr($xword,-1,1) == '*'){
            $xword = substr($xword,0,-1);
            $wild += 2;
            $wlen -= 1;
        }
        if ($wlen < 3 && $wild == 0 && !is_numeric($xword)) continue;

        // look for the ID(s) for the given word
        if($wild){  // handle wildcard search
            foreach (idx_indexLengths($wlen) as $ixlen){
                $word_idx = idx_getIndex('w',$ixlen);
                $cnt = count($word_idx);
                for($wid=0; $wid<$cnt; $wid++){
                    $iword = $word_idx[$wid];
                    if( (($wild==3) && is_int(strpos($iword,$xword))) ||
#                        (($wild==1) && ("$xword\n" == substr($iword,$l))) ||
                        (($wild==1) && preg_match($ptn,$iword)) ||
#                        (($wild==2) && ($xword == substr($iword,0,strlen($xword))))
                        (($wild==2) && (0 === strpos($iword,$xword)))

                      ){
                        if(!isset($wids[$ixlen])) $wids[$ixlen] = array();
                        $wids[$ixlen][] = $wid;
                        $result[$word][] = "$ixlen*$wid";
                    }
                }
            }
        }else{     // handle exact search
            $word_idx = idx_getIndex('w',$wlen);
            $wid = array_search("$word\n",$word_idx);
            if(is_int($wid)){
                $wids[$wlen] = array($wid);
                $result[$word][] = "$wlen*$wid";
            }else{
                $result[$word] = array();
            }
        }
    }

    $docs = array();                          // hold docs found
    foreach(array_keys($wids) as $wlen){
        sort($wids[$wlen]);
        $wids[$wlen] = array_unique($wids[$wlen]);

        // Open index
        idx_touchIndex('i',$wlen);
        $idx = fopen($conf['indexdir']."/i$wlen.idx",'r');
        if(!$idx){
            msg("Failed to open index file",-1);
            return false;
        }

        // Walk the index til the lines are found
        $lno  = 0;
        $line = '';
        $ixids =& $wids[$wlen];
        $srch = array_shift($ixids);               // which word do we look for?
        while (!feof($idx)) {
            // read full line
            $line .= fgets($idx, 4096);
            if(substr($line,-1) != "\n") continue;
            if($lno > $srch)             break;   // shouldn't happen

            // do we want this line?
            if($lno == $srch){
                // add docs to list
                $docs["$wlen*$srch"] = idx_parseIndexLine($page_idx,$line);

                $srch = array_shift($ixids);        // next word to look up
                if($srch == null) break;           // no more words
            }

            $line = ''; // reset line buffer
            $lno++;     // increase linecounter
        }
        fclose($idx);
    }


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
