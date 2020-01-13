<?php
namespace dokuwiki\Search;

use dokuwiki\Search\PagewordIndex;
use dokuwiki\Search\MetadataIndex;
use dokuwiki\Extension\Event;

// Version tag used to force rebuild on upgrade
const INDEXER_VERSION = 8;


/**
 * Class DokuWiki Page Index (Singleton)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class PageIndex extends AbstractIndex
{
    /** @var PageIndex */
    protected static $instance = null;

    /** @var MetadataIndex */
    protected $MetadataIndex = null;

    /** @var PagewordIndex */
    protected $PagewordIndex = null;

    /**
     * PageIndex constructor. Singleton, thus protected!
     */
    protected function __construct() {
        $this->MetadataIndex = MetadataIndex::getInstance();
        $this->PagewordIndex = PagewordIndex::getInstance();
    }

    /**
     * Get new or existing singleton instance of the PageIndex
     *
     * @return PageIndex
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

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
    public function getVersion()
    {
        static $indexer_version = null;
        if ($indexer_version == null) {
            $version = INDEXER_VERSION;

            // DokuWiki version is included for the convenience of plugins
            $data = array('dokuwiki' => $version);
            Event::createAndTrigger('INDEXER_VERSION_GET', $data, null, false);
            unset($data['dokuwiki']); // this needs to be first
            ksort($data);
            foreach ($data as $plugin => $vers) {
                $version .= '+'.$plugin.'='.$vers;
            }
            $indexer_version = $version;
        }
        return $indexer_version;
    }

    /**
     * Return a list of all pages
     * Warning: pages may not exist!
     *
     * @param string    $key    list only pages containing the metadata key (optional)
     * @return array            list of page names
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function getPages($key = null)
    {
        $page_idx = $this->getIndex('page', '');
        if (is_null($key)) return $page_idx;

        $metaname = $this->cleanName($key);

        // Special handling for titles
        if ($key == 'title') {
            $title_idx = $this->getIndex('title', '');
            array_splice($page_idx, count($title_idx));
            foreach ($title_idx as $i => $title) {
                if ($title === '') unset($page_idx[$i]);
            }
            return array_values($page_idx);
        }

        $pages = array();
        $lines = $this->getIndex($metaname.'_i', '');
        foreach ($lines as $line) {
            $pages = array_merge($pages, $this->parseTuples($page_idx, $line));
        }
        return array_keys($pages);
    }

    /**
     * Find pages in the fulltext index containing the words,
     *
     * The search words must be pre-tokenized, meaning only letters and
     * numbers with an optional wildcard
     *
     * The returned array will have the original tokens as key. The values
     * in the returned list is an array with the page names as keys and the
     * number of times that token appears on the page as value.
     *
     * @param array  $tokens list of words to search for
     * @return array         list of page names with usage counts
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function lookup(&$tokens)
    {
        $result = array();
        $wids = $this->PagewordIndex->getIndexWords($tokens, $result);
        if (empty($wids)) return array();
        // load known words and documents
        $page_idx = $this->getIndex('page', '');
        $docs = array();
        foreach (array_keys($wids) as $wlen) {
            $wids[$wlen] = array_unique($wids[$wlen]);
            $index = $this->getIndex('i', $wlen);
            foreach ($wids[$wlen] as $ixid) {
                if ($ixid < count($index)) {
                    $docs["{$wlen}*{$ixid}"] = $this->parseTuples($page_idx, $index[$ixid]);
                }
            }
        }
        // merge found pages into final result array
        $final = array();
        foreach ($result as $word => $res) {
            $final[$word] = array();
            foreach ($res as $wid) {
                // handle the case when ($ixid < count($index)) has been false
                // and thus $docs[$wid] hasn't been set.
                if (!isset($docs[$wid])) continue;
                $hits =& $docs[$wid];
                foreach ($hits as $hitkey => $hitcnt) {
                    // make sure the document still exists
                    if (!page_exists($hitkey, '', false)) continue;
                    if (!isset($final[$word][$hitkey])) {
                        $final[$word][$hitkey] = $hitcnt;
                    } else {
                        $final[$word][$hitkey] += $hitcnt;
                    }
                }
            }
        }
        return $final;
    }

    /**
     * Adds/updates the search index for the given page
     *
     * Locking is handled internally.
     *
     * @param string        $page   name of the page to index
     * @param bool          $verbose    print status messages
     * @param bool          $force  force reindexing even when the index is up to date
     * @return bool  If the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function addPage($page, $verbose = false, $force = false)
    {
        $idxtag = metaFN($page,'.indexed');
        // check if page was deleted but is still in the index
        if (!page_exists($page)) {
            if (!file_exists($idxtag)) {
                if ($verbose) print("Indexer: $page does not exist, ignoring".DOKU_LF);
                return false;
            }
            $result = $this->deletePage($page);
            if (!$result && $this->errors) {
                if ($verbose) print("Indexer: locked".DOKU_LF);
                return false;
            }
            @unlink($idxtag);
            return $result;
        }

        // check if indexing needed
        if (!$force && file_exists($idxtag)) {
            if (trim(io_readFile($idxtag)) == $this->getVersion()) {
                $last = @filemtime($idxtag);
                if ($last > @filemtime(wikiFN($page))) {
                    if ($verbose) print("Indexer: index for $page up to date".DOKU_LF);
                    return false;
                }
            }
        }

        $indexenabled = p_get_metadata($page, 'internal index', METADATA_RENDER_UNLIMITED);
        if ($indexenabled === false) {
            $result = false;
            if (file_exists($idxtag)) {
                $result = $this->deletePage($page);
                if (!$result && $this->errors) {
                    if ($verbose) print("Indexer: locked".DOKU_LF);
                    return false;
                }
                @unlink($idxtag);
            }
            if ($verbose) print("Indexer: index disabled for $page".DOKU_LF);
            return $result;
        }

        $pid = $this->getPID($page);
        if ($pid === false) {
            if ($verbose) print("Indexer: getting the PID failed for $page".DOKU_LF);
            return false;
        }
        $body = '';
        $metadata = array();
        $metadata['title'] = p_get_metadata($page, 'title', METADATA_RENDER_UNLIMITED);

        $references = p_get_metadata($page, 'relation references', METADATA_RENDER_UNLIMITED);
        $metadata['relation_references'] = ($references !== null) ?
                array_keys($references) : array();

        $media = p_get_metadata($page, 'relation media', METADATA_RENDER_UNLIMITED);
        $metadata['relation_media'] = ($media !== null) ?
                array_keys($media) : array();

        $data = compact('page', 'body', 'metadata', 'pid');
        $evt = new Event('INDEXER_PAGE_ADD', $data);
        if ($evt->advise_before()) $data['body'] = $data['body'].' '.rawWiki($page);
        $evt->advise_after();
        unset($evt);
        extract($data);

        $result = $this->PagewordIndex->addPageWords($page, $body);
        if (!$result && $this->errors) {
            if ($verbose) print("Indexer: locked".DOKU_LF);
            return false;
        }

        if ($result) {
            $result = $this->MetadataIndex->addMetaKeys($page, $metadata);
            if (!$result && $this->errors) {
                if ($verbose) print("Indexer: locked".DOKU_LF);
                return false;
            }
        }

        if ($result) {
            io_saveFile(metaFN($page,'.indexed'), $this->getVersion());
            if ($verbose) {
                print("Indexer: finished".DOKU_LF);
                return true;
            }
        }

        return $result;
    }

    /**
     * Rename a page in the search index without changing the indexed content.
     * This function doesn't check if the old or new name exists in the filesystem.
     * It returns an error if the old page isn't in the page list of the indexer
     * and it deletes all previously indexed content of the new page.
     *
     * @param string $oldpage The old page name
     * @param string $newpage The new page name
     * @return bool           If the page was successfully renamed
     */
    public function renamePage($oldpage, $newpage)
    {
        if (!$this->lock()) return false;  // set $errors property

        $pages = $this->getPages();

        $id = array_search($oldpage, $pages, true);
        if ($id === false) {
            $this->unlock();
            static::$errors[] = 'page is not in index';
            return false;
        }

        $new_id = array_search($newpage, $pages, true);
        if ($new_id !== false) {
            // make sure the page is not in the index anymore
            if ($this->deletePageNoLock($newpage) !== true) {
                return false;
            }

            $pages[$new_id] = 'deleted:'.time().rand(0, 9999);
        }

        $pages[$id] = $newpage;

        // update index
        if (!$this->saveIndex('page', '', $pages)) {
            $this->unlock();
            return false;
        }

        // reset the pid cache
        $this->resetPIDCache();

        $this->unlock();
        return true;
    }

    /**
     * Remove a page from the index
     *
     * Erases entries in all known indexes.
     *
     * @param string    $page   a page name
     * @return bool             If the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function deletePage($page)
    {
        if (!$this->lock()) return false;  // set $errors property

        $result = $this->deletePageNoLock($page);
        $this->unlock();
        return $result;
    }

    /**
     * Remove a page from the index without locking the index,
     * only use this function if the index is already locked
     *
     * Erases entries in all known indexes.
     *
     * @param string    $page   a page name
     * @return bool             If the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function deletePageNoLock($page)
    {
        // remove obsolete pageword index entries
        $result = $this->PagewordIndex->deletePageWordsNoLock($page);
        if (!$result) {
            return false;
        }

        // delete all keys of the page from metadata index
        $result = $this->MetadataIndex->deleteMetaKeysNoLock($page, '');
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Clear the Page Index
     *
     * @param bool   $requireLock
     * @return bool  If the index has been cleared successfully
     */
    public function clear($requireLock = true)
    {
        global $conf;

        if ($requireLock && !$this->lock()) return false;

        // clear Metadata Index
        $this->MetadataIndex->clear(false);

        // clear Pageword Index
        $this->PagewordIndex->clear(false);

        @unlink($conf['indexdir'].'/page.idx');

        // clear the pid cache
        $this->resetPIDCache();

        if ($requireLock) $this->unlock();
        return true;
    }
}
