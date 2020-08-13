<?php
/**
 * Functions to create the fulltext search index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Tom N Harris <tnharris@whoopdedo.org>
 */

use dokuwiki\Extension\Event;
use dokuwiki\Search\Indexer;

// Version tag used to force rebuild on upgrade
define('INDEXER_VERSION', 8);

// set the minimum token length to use in the index (note, this doesn't apply to numeric tokens)
if (!defined('IDX_MINWORDLENGTH')) define('IDX_MINWORDLENGTH',2);

/**
 * Version of the indexer taking into consideration the external tokenizer.
 * The indexer is only compatible with data written by the same version.
 *
 * @triggers INDEXER_VERSION_GET
 * Plugins that modify what gets indexed should hook this event and
 * add their version info to the event data like so:
 *     $data[$plugin_name] = $plugin_version;
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 * @author Michael Hamann <michael@content-space.de>
 *
 * @return int|string
 */
function idx_get_version(){
    static $indexer_version = null;
    if ($indexer_version == null) {
        $version = INDEXER_VERSION;

        // DokuWiki version is included for the convenience of plugins
        $data = array('dokuwiki'=>$version);
        Event::createAndTrigger('INDEXER_VERSION_GET', $data, null, false);
        unset($data['dokuwiki']); // this needs to be first
        ksort($data);
        foreach ($data as $plugin=>$vers)
            $version .= '+'.$plugin.'='.$vers;
        $indexer_version = $version;
    }
    return $indexer_version;
}

/**
 * Measure the length of a string.
 * Differs from strlen in handling of asian characters.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 *
 * @param string $w
 * @return int
 */
function wordlen($w){
    $l = strlen($w);
    // If left alone, all chinese "words" will get put into w3.idx
    // So the "length" of a "word" is faked
    if(preg_match_all('/[\xE2-\xEF]/',$w,$leadbytes)) {
        foreach($leadbytes[0] as $b)
            $l += ord($b) - 0xE1;
    }
    return $l;
}

/**
 * Create an instance of the indexer.
 *
 * @return Indexer    an Indexer
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_get_indexer() {
    static $Indexer;
    if (!isset($Indexer)) {
        $Indexer = new Indexer();
    }
    return $Indexer;
}

/**
 * Returns words that will be ignored.
 *
 * @return array                list of stop words
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function & idx_get_stopwords() {
    static $stopwords = null;
    if (is_null($stopwords)) {
        global $conf;
        $swfile = DOKU_INC.'inc/lang/'.$conf['lang'].'/stopwords.txt';
        if(file_exists($swfile)){
            $stopwords = file($swfile, FILE_IGNORE_NEW_LINES);
        }else{
            $stopwords = array();
        }
    }
    return $stopwords;
}

/**
 * Adds/updates the search index for the given page
 *
 * Locking is handled internally.
 *
 * @param string        $page   name of the page to index
 * @param boolean       $verbose    print status messages
 * @param boolean       $force  force reindexing even when the index is up to date
 * @return string|boolean  the function completed successfully
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
function idx_addPage($page, $verbose=false, $force=false) {
    $idxtag = metaFN($page,'.indexed');
    // check if page was deleted but is still in the index
    if (!page_exists($page)) {
        if (!file_exists($idxtag)) {
            if ($verbose) print("Indexer: $page does not exist, ignoring".DOKU_LF);
            return false;
        }
        $Indexer = idx_get_indexer();
        $result = $Indexer->deletePage($page);
        if ($result === "locked") {
            if ($verbose) print("Indexer: locked".DOKU_LF);
            return false;
        }
        @unlink($idxtag);
        return $result;
    }

    // check if indexing needed
    if(!$force && file_exists($idxtag)){
        if(trim(io_readFile($idxtag)) == idx_get_version()){
            $last = @filemtime($idxtag);
            if($last > @filemtime(wikiFN($page))){
                if ($verbose) print("Indexer: index for $page up to date".DOKU_LF);
                return false;
            }
        }
    }

    $indexenabled = p_get_metadata($page, 'internal index', METADATA_RENDER_UNLIMITED);
    if ($indexenabled === false) {
        $result = false;
        if (file_exists($idxtag)) {
            $Indexer = idx_get_indexer();
            $result = $Indexer->deletePage($page);
            if ($result === "locked") {
                if ($verbose) print("Indexer: locked".DOKU_LF);
                return false;
            }
            @unlink($idxtag);
        }
        if ($verbose) print("Indexer: index disabled for $page".DOKU_LF);
        return $result;
    }

    $Indexer = idx_get_indexer();
    $pid = $Indexer->getPID($page);
    if ($pid === false) {
        if ($verbose) print("Indexer: getting the PID failed for $page".DOKU_LF);
        return false;
    }
    $body = '';
    $metadata = array();
    $metadata['title'] = p_get_metadata($page, 'title', METADATA_RENDER_UNLIMITED);
    if (($references = p_get_metadata($page, 'relation references', METADATA_RENDER_UNLIMITED)) !== null)
        $metadata['relation_references'] = array_keys($references);
    else
        $metadata['relation_references'] = array();

    if (($media = p_get_metadata($page, 'relation media', METADATA_RENDER_UNLIMITED)) !== null)
        $metadata['relation_media'] = array_keys($media);
    else
        $metadata['relation_media'] = array();

    $data = compact('page', 'body', 'metadata', 'pid');
    $evt = new Event('INDEXER_PAGE_ADD', $data);
    if ($evt->advise_before()) $data['body'] = $data['body'] . " " . rawWiki($page);
    $evt->advise_after();
    unset($evt);
    extract($data);

    $result = $Indexer->addPageWords($page, $body);
    if ($result === "locked") {
        if ($verbose) print("Indexer: locked".DOKU_LF);
        return false;
    }

    if ($result) {
        $result = $Indexer->addMetaKeys($page, $metadata);
        if ($result === "locked") {
            if ($verbose) print("Indexer: locked".DOKU_LF);
            return false;
        }
    }

    if ($result)
        io_saveFile(metaFN($page,'.indexed'), idx_get_version());
    if ($verbose) {
        print("Indexer: finished".DOKU_LF);
        return true;
    }
    return $result;
}

/**
 * Find tokens in the fulltext index
 *
 * Takes an array of words and will return a list of matching
 * pages for each one.
 *
 * Important: No ACL checking is done here! All results are
 *            returned, regardless of permissions
 *
 * @param array      $words  list of words to search for
 * @return array             list of pages found, associated with the search terms
 */
function idx_lookup(&$words) {
    $Indexer = idx_get_indexer();
    return $Indexer->lookup($words);
}

/**
 * Split a string into tokens
 *
 * @param string $string
 * @param bool $wc
 *
 * @return array
 */
function idx_tokenizer($string, $wc=false) {
    $Indexer = idx_get_indexer();
    return $Indexer->tokenizer($string, $wc);
}

/* For compatibility */

/**
 * Read the list of words in an index (if it exists).
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 *
 * @param string $idx
 * @param string $suffix
 * @return array
 */
function idx_getIndex($idx, $suffix) {
    global $conf;
    $fn = $conf['indexdir'].'/'.$idx.$suffix.'.idx';
    if (!file_exists($fn)) return array();
    return file($fn);
}

/**
 * Get the list of lengths indexed in the wiki.
 *
 * Read the index directory or a cache file and returns
 * a sorted array of lengths of the words used in the wiki.
 *
 * @author YoBoY <yoboy.leguesh@gmail.com>
 *
 * @return array
 */
function idx_listIndexLengths() {
    global $conf;
    // testing what we have to do, create a cache file or not.
    if ($conf['readdircache'] == 0) {
        $docache = false;
    } else {
        clearstatcache();
        if (file_exists($conf['indexdir'].'/lengths.idx')
        && (time() < @filemtime($conf['indexdir'].'/lengths.idx') + $conf['readdircache'])) {
            if (
                ($lengths = @file($conf['indexdir'].'/lengths.idx', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
                !== false
            ) {
                $idx = array();
                foreach ($lengths as $length) {
                    $idx[] = (int)$length;
                }
                return $idx;
            }
        }
        $docache = true;
    }

    if ($conf['readdircache'] == 0 || $docache) {
        $dir = @opendir($conf['indexdir']);
        if ($dir === false)
            return array();
        $idx = array();
        while (($f = readdir($dir)) !== false) {
            if (substr($f, 0, 1) == 'i' && substr($f, -4) == '.idx') {
                $i = substr($f, 1, -4);
                if (is_numeric($i))
                    $idx[] = (int)$i;
            }
        }
        closedir($dir);
        sort($idx);
        // save this in a file
        if ($docache) {
            $handle = @fopen($conf['indexdir'].'/lengths.idx', 'w');
            @fwrite($handle, implode("\n", $idx));
            @fclose($handle);
        }
        return $idx;
    }

    return array();
}

/**
 * Get the word lengths that have been indexed.
 *
 * Reads the index directory and returns an array of lengths
 * that there are indices for.
 *
 * @author YoBoY <yoboy.leguesh@gmail.com>
 *
 * @param array|int $filter
 * @return array
 */
function idx_indexLengths($filter) {
    global $conf;
    $idx = array();
    if (is_array($filter)) {
        // testing if index files exist only
        $path = $conf['indexdir']."/i";
        foreach ($filter as $key => $value) {
            if (file_exists($path.$key.'.idx'))
                $idx[] = $key;
        }
    } else {
        $lengths = idx_listIndexLengths();
        foreach ($lengths as $key => $length) {
            // keep all the values equal or superior
            if ((int)$length >= (int)$filter)
                $idx[] = $length;
        }
    }
    return $idx;
}

/**
 * Clean a name of a key for use as a file name.
 *
 * Romanizes non-latin characters, then strips away anything that's
 * not a letter, number, or underscore.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 *
 * @param string $name
 * @return string
 */
function idx_cleanName($name) {
    $name = \dokuwiki\Utf8\Clean::romanize(trim((string)$name));
    $name = preg_replace('#[ \./\\:-]+#', '_', $name);
    $name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
    return strtolower($name);
}

//Setup VIM: ex: et ts=4 :
