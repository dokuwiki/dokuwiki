<?php
/**
 * Common DokuWiki functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_CONF.'dokuwiki.php');
  require_once(DOKU_INC.'inc/io.php');
  require_once(DOKU_INC.'inc/utf8.php');
  require_once(DOKU_INC.'inc/parserutils.php');

// Asian characters are handled as words. The following regexp defines the
// Unicode-Ranges for Asian characters
// Ranges taken from http://en.wikipedia.org/wiki/Unicode_block
// I'm no language expert. If you think some ranges are wrongly chosen or
// a range is missing, please contact me
define('IDX_ASIAN1','[\x{0E00}-\x{0E7F}]'); // Thai
define('IDX_ASIAN2','['.
                   '\x{2E80}-\x{3040}'.  // CJK -> Hangul
                   '\x{309D}-\x{30A0}'.
                   '\x{30FD}-\x{31EF}\x{3200}-\x{D7AF}'.
                   '\x{F900}-\x{FAFF}'.  // CJK Compatibility Ideographs
                   '\x{FE30}-\x{FE4F}'.  // CJK Compatibility Forms
                   ']');
define('IDX_ASIAN3','['.                // Hiragana/Katakana (can be two characters)
                   '\x{3042}\x{3044}\x{3046}\x{3048}'.
                   '\x{304A}-\x{3062}\x{3064}-\x{3082}'.
                   '\x{3084}\x{3086}\x{3088}-\x{308D}'.
                   '\x{308F}-\x{3094}'.
                   '\x{30A2}\x{30A4}\x{30A6}\x{30A8}'.
                   '\x{30AA}-\x{30C2}\x{30C4}-\x{30E2}'.
                   '\x{30E4}\x{30E6}\x{30E8}-\x{30ED}'.
                   '\x{30EF}-\x{30F4}\x{30F7}-\x{30FA}'.
                   ']['.
                   '\x{3041}\x{3043}\x{3045}\x{3047}\x{3049}'.
                   '\x{3063}\x{3083}\x{3085}\x{3087}\x{308E}\x{3095}-\x{309C}'.
                   '\x{30A1}\x{30A3}\x{30A5}\x{30A7}\x{30A9}'.
                   '\x{30C3}\x{30E3}\x{30E5}\x{30E7}\x{30EE}\x{30F5}\x{30F6}\x{30FB}\x{30FC}'.
                   '\x{31F0}-\x{31FF}'.
                   ']?');
define('IDX_ASIAN', '(?:'.IDX_ASIAN1.'|'.IDX_ASIAN2.'|'.IDX_ASIAN3.')');

/**
 * Measure the length of a string.
 * Differs from strlen in handling of asian characters.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function wordlen($w){
    $l = strlen($w);
    // If left alone, all chinese "words" will get put into w3.idx
    // So the "length" of a "word" is faked
    if(preg_match('/'.IDX_ASIAN2.'/u',$w))
        $l += ord($w) - 0xE1;  // Lead bytes from 0xE2-0xEF
    return $l;
}

/**
 * Write a list of strings to an index file.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_saveIndex($pre, $wlen, &$idx){
    global $conf;
    $fn = $conf['indexdir'].'/'.$pre.$wlen;
    $fh = @fopen($fn.'.tmp','w');
    if(!$fh) return false;
    foreach ($idx as $line) {
        fwrite($fh,$line);
    }
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
 * FIXME: This function isn't currently used. It will probably be removed soon.
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
 * Read a line ending with \n.
 * Returns false on EOF.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function _freadline($fh) {
    if (feof($fh)) return false;
    $ln = '';
    while (($buf = fgets($fh,4096)) !== false) {
        $ln .= $buf;
        if (substr($buf,-1) == "\n") break;
    }
    if ($ln === '') return false;
    if (substr($ln,-1) != "\n") $ln .= "\n";
    return $ln;
}

/**
 * Write a line to an index file.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_saveIndexLine($pre, $wlen, $idx, $line){
    global $conf;
    if(substr($line,-1) != "\n") $line .= "\n";
    $fn = $conf['indexdir'].'/'.$pre.$wlen;
    $fh = @fopen($fn.'.tmp','w');
    if(!$fh) return false;
    $ih = @fopen($fn.'.idx','r');
    if ($ih) {
        $ln = -1;
        while (($curline = _freadline($ih)) !== false) {
            if (++$ln == $idx) {
                fwrite($fh, $line);
            } else {
                fwrite($fh, $curline);
            }
        }
        if ($idx > $ln) {
            fwrite($fh,$line);
        }
        fclose($ih);
    } else {
        fwrite($fh,$line);
    }
    fclose($fh);
    if($conf['fperm']) chmod($fn.'.tmp', $conf['fperm']);
    io_rename($fn.'.tmp', $fn.'.idx');
    return true;
}

/**
 * Read a single line from an index (if it exists).
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_getIndexLine($pre, $wlen, $idx){
    global $conf;
    $fn = $conf['indexdir'].'/'.$pre.$wlen.'.idx';
    if(!@file_exists($fn)) return '';
    $fh = @fopen($fn,'r');
    if(!$fh) return '';
    $ln = -1;
    while (($line = _freadline($fh)) !== false) {
        if (++$ln == $idx) break;
    }
    fclose($fh);
    return "$line";
}

/**
 * Split a page into words
 *
 * Returns an array of word counts, false if an error occurred.
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

    $body = '';
    $data = array($page, $body);
    $evt = new Doku_Event('INDEXER_PAGE_ADD', $data);
    if ($evt->advise_before()) $data[1] .= rawWiki($page);
    $evt->advise_after();
    unset($evt);

    list($page,$body) = $data;
    
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
            $l = wordlen($w);
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
                $wid = count($word_idx);
                $word_idx[] = "$word\n";
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
        if (!idx_saveIndex('page','',$page_idx)){
            trigger_error("Failed to write page index", E_USER_ERROR);
            return false;
        }
    }

    $pagewords = array();
    // get word usage in page
    $words = idx_getPageWords($page);
    if($words === false) return false;

    if(!empty($words)) {
        foreach(array_keys($words) as $wlen){
            $index = idx_getIndex('i',$wlen);
            foreach($words[$wlen] as $wid => $freq){
                if($wid<count($index)){
                    $index[$wid] = idx_updateIndexLine($index[$wid],$pid,$freq);
                }else{
                    // New words **should** have been added in increasing order
                    // starting with the first unassigned index.
                    // If someone can show how this isn't true, then I'll need to sort
                    // or do something special.
                    $index[$wid] = idx_updateIndexLine('',$pid,$freq);
                }
                $pagewords[] = "$wlen*$wid";
            }
            // save back word index
            if(!idx_saveIndex('i',$wlen,$index)){
                trigger_error("Failed to write index", E_USER_ERROR);
                return false;
            }
        }
    }
    
    // Remove obsolete index entries
    $pageword_idx = trim(idx_getIndexLine('pageword','',$pid));
    if ($pageword_idx !== '') {
        $oldwords = explode(':',$pageword_idx);
        $delwords = array_diff($oldwords, $pagewords);
        $upwords = array();
        foreach ($delwords as $word) {
            if($word=='') continue;
            list($wlen,$wid) = explode('*',$word);
            $wid = (int)$wid;
            $upwords[$wlen][] = $wid;
        }
        foreach ($upwords as $wlen => $widx) {
            $index = idx_getIndex('i',$wlen);
            foreach ($widx as $wid) {
                $index[$wid] = idx_updateIndexLine($index[$wid],$pid,0);
            }
            idx_saveIndex('i',$wlen,$index);
        }
    }
    // Save the reverse index
    $pageword_idx = join(':',$pagewords)."\n";
    if(!idx_saveIndexLine('pageword','',$pid,$pageword_idx)){
        trigger_error("Failed to write word index", E_USER_ERROR);
        return false;
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
 * @deprecated - see idx_updateIndexLine
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_writeIndexLine($fh,$line,$pid,$count){
    fwrite($fh,idx_updateIndexLine($line,$pid,$count));
}

/**
 * Modify an index line with new information
 *
 * This returns a line of the index. It removes the
 * given document from the line and readds it if
 * $count is >0.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function idx_updateIndexLine($line,$pid,$count){
    $line = trim($line);
    $updated = array();
    if($line != ''){
        $parts = explode(':',$line);
        // remove doc from given line
        foreach($parts as $part){
            if($part == '') continue;
            list($doc,$cnt) = explode('*',$part);
            if($doc != $pid){
                $updated[] = $part;
            }
        }
    }

    // add doc
    if ($count){
        $updated[] = "$pid*$count";
    }

    return join(':',$updated)."\n";
}

/**
 * Get the word lengths that have been indexed.
 *
 * Reads the index directory and returns an array of lengths
 * that there are indices for.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_indexLengths(&$filter){
    global $conf;
    $dir = @opendir($conf['indexdir']);
    if($dir===false)
        return array();
    $idx = array();
    if(is_array($filter)){
        while (($f = readdir($dir)) !== false) {
            if (substr($f,0,1) == 'i' && substr($f,-4) == '.idx'){
                $i = substr($f,1,-4);
                if (is_numeric($i) && isset($filter[(int)$i]))
                    $idx[] = (int)$i;
            }
        }
    }else{
        // Exact match first.
        if(@file_exists($conf['indexdir']."/i$filter.idx"))
            $idx[] = $filter;
        while (($f = readdir($dir)) !== false) {
            if (substr($f,0,1) == 'i' && substr($f,-4) == '.idx'){
                $i = substr($f,1,-4);
                if (is_numeric($i) && $i > $filter)
                    $idx[] = (int)$i;
            }
        }
    }
    closedir($dir);
    return $idx;
}

/**
 * Find the the index number of each search term.
 *
 * This will group together words that appear in the same index.
 * So it should perform better, because it only opens each index once.
 * Actually, it's not that great. (in my experience) Probably because of the disk cache.
 * And the sorted function does more work, making it slightly slower in some cases.
 *
 * @param array    $words   The query terms. Words should only contain valid characters,
 *                          with a '*' at either the beginning or end of the word (or both)
 * @param arrayref $result  Set to word => array("length*id" ...), use this to merge the
 *                          index locations with the appropriate query term.
 * @return array            Set to length => array(id ...)
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_getIndexWordsSorted($words,&$result){
    // parse and sort tokens
    $tokens = array();
    $tokenlength = array();
    $tokenwild = array();
    foreach($words as $word){
        $result[$word] = array();
        $wild = 0;
        $xword = $word;
        $wlen = wordlen($word);

        // check for wildcards
        if(substr($xword,0,1) == '*'){
            $xword = substr($xword,1);
            $wild |= 1;
            $wlen -= 1;
        }
        if(substr($xword,-1,1) == '*'){
            $xword = substr($xword,0,-1);
            $wild |= 2;
            $wlen -= 1;
        }
        if ($wlen < 3 && $wild == 0 && !is_numeric($xword)) continue;
        if(!isset($tokens[$xword])){
            $tokenlength[$wlen][] = $xword;
        }
        if($wild){
            $ptn = preg_quote($xword,'/');
            if(($wild&1) == 0) $ptn = '^'.$ptn;
            if(($wild&2) == 0) $ptn = $ptn.'$';
            $tokens[$xword][] = array($word, '/'.$ptn.'/');
            if(!isset($tokenwild[$xword])) $tokenwild[$xword] = $wlen;
        }else
            $tokens[$xword][] = array($word, null);
    }
    asort($tokenwild);
    // $tokens = array( base word => array( [ query word , grep pattern ] ... ) ... )
    // $tokenlength = array( base word length => base word ... )
    // $tokenwild = array( base word => base word length ... )

    $length_filter = empty($tokenwild) ? $tokenlength : min(array_keys($tokenlength));
    $indexes_known = idx_indexLengths($length_filter);
    if(!empty($tokenwild)) sort($indexes_known);
    // get word IDs
    $wids = array();
    foreach($indexes_known as $ixlen){
        $word_idx = idx_getIndex('w',$ixlen);
        // handle exact search
        if(isset($tokenlength[$ixlen])){
            foreach($tokenlength[$ixlen] as $xword){
                $wid = array_search("$xword\n",$word_idx);
                if(is_int($wid)){
                    $wids[$ixlen][] = $wid;
                    foreach($tokens[$xword] as $w)
                        $result[$w[0]][] = "$ixlen*$wid";
                }
            }
        }
        // handle wildcard search
        foreach($tokenwild as $xword => $wlen){
            if($wlen >= $ixlen) break;
            foreach($tokens[$xword] as $w){
                if(is_null($w[1])) continue;
                foreach(array_keys(preg_grep($w[1],$word_idx)) as $wid){
                    $wids[$ixlen][] = $wid;
                    $result[$w[0]][] = "$ixlen*$wid";
                }
            }
        }
    }
  return $wids;
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

    $wids = idx_getIndexWordsSorted($words, $result);
    if(empty($wids)) return array();

    // load known words and documents
    $page_idx = idx_getIndex('page','');

    $docs = array();                          // hold docs found
    foreach(array_keys($wids) as $wlen){
        $wids[$wlen] = array_unique($wids[$wlen]);
        $index = idx_getIndex('i',$wlen);
        foreach($wids[$wlen] as $ixid){
            if($ixid < count($index))
                $docs["$wlen*$ixid"] = idx_parseIndexLine($page_idx,$index[$ixid]);
        }
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
        if(!page_exists($doc,'',false)) continue;

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
 */
function idx_tokenizer($string,&$stopwords,$wc=false){
    $words = array();
    $wc = ($wc) ? '' : $wc = '\*';

    if(preg_match('/[^0-9A-Za-z]/u', $string)){
        // handle asian chars as single words (may fail on older PHP version)
        $asia = @preg_replace('/('.IDX_ASIAN.')/u',' \1 ',$string);
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

/**
 * Create a pagewords index from the existing index.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_upgradePageWords(){
    global $conf;
    $page_idx = idx_getIndex('page','');
    if (empty($page_idx)) return;
    $pagewords = array();
    for ($n=0;$n<count($page_idx);$n++) $pagewords[] = array();
    unset($page_idx);

    $n=0;
    foreach (idx_indexLengths($n) as $wlen) {
        $lines = idx_getIndex('i',$wlen);
        for ($wid=0;$wid<count($lines);$wid++) {
            $wkey = "$wlen*$wid";
            foreach (explode(':',trim($lines[$wid])) as $part) {
                if($part == '') continue;
                list($doc,$cnt) = explode('*',$part);
                $pagewords[(int)$doc][] = $wkey;
            }
        }
    }

    $fn = $conf['indexdir'].'/pageword';
    $fh = @fopen($fn.'.tmp','w');
    if (!$fh){
        trigger_error("Failed to write word index", E_USER_ERROR);
        return false;
    }
    foreach ($pagewords as $line){
        fwrite($fh, join(':',$line)."\n");
    }
    fclose($fh);
    if($conf['fperm']) chmod($fn.'.tmp', $conf['fperm']);
    io_rename($fn.'.tmp', $fn.'.idx');
    return true;
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
