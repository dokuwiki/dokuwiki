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

    /**
     * PageIndex constructor. Singleton, thus protected!
     */
    protected function __construct() {}

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
            if (!$result && !empty(static::$errors)) {
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
                if (!$result && !empty(static::$errors)) {
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

        // Access to Pageword Index
        $PagewordIndex = PagewordIndex::getInstance();
        $result = $PagewordIndex->addPageWords($page, $body);
        if (!$result && !empty(static::$errors)) {
            if ($verbose) print("Indexer: locked".DOKU_LF);
            return false;
        }

        if ($result) {
            // Access to Metadata Index
            $MetadataIndex = MetadataIndex::getInstance();
            $result = $MetadataIndex->addMetaKeys($page, $metadata);
            if (!$result && !empty(static::$errors)) {
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
            static::$errors[] = "$oldpage is not found in index";
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
     * Erases entries in all known indexes. Locking is handled internally.
     *
     * @param string    $page   a page name
     * @return bool             If the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function deletePage($page)
    {
        // remove obsolete pageword index entries
        $PagewordIndex = PagewordIndex::getInstance();
        $result = $PagewordIndex->deletePageWords($page);
        if (!$result) {
            return false;
        }

        // delete all keys of the page from metadata index
        $MetadataIndex = MetadataIndex::getInstance();
        $result = $MetadataIndex->deleteMetaKeys($page);
        if (!$result) {
            return false;
        }

        return true;
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
        $PagewordIndex = PagewordIndex::getInstance();
        $MetadataIndex = MetadataIndex::getInstance();

        return $PagewordIndex->deletePageWordsNoLock($page)
            && $MetadataIndex->deleteMetaKeysNoLock($page);
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
        $MetadataIndex = MetadataIndex::getInstance();
        $MetadataIndex->clear(false);

        // clear Pageword Index
        $PagewordIndex = PagewordIndex::getInstance();
        $PagewordIndex->clear(false);

        @unlink($conf['indexdir'].'/page.idx');

        // clear the pid cache
        $this->resetPIDCache();

        if ($requireLock) $this->unlock();
        return true;
    }


    /**
     * Return a list of words sorted by number of times used
     *
     * @param int       $min    bottom frequency threshold
     * @param int       $max    upper frequency limit. No limit if $max<$min
     * @param int       $minlen minimum length of words to count
     * @param string    $key    metadata key to list. Uses the fulltext index if not given
     * @return array            list of words as the keys and frequency as values
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function histogram($min=1, $max=0, $minlen=3, $key=null)
    {
        if ($min < 1)    $min = 1;
        if ($max < $min) $max = 0;

        $result = array();

        if ($key == 'title') {
            $index = $this->getIndex('title', '');
            $index = array_count_values($index);
            foreach ($index as $val => $cnt) {
                if ($cnt >= $min && (!$max || $cnt <= $max) && strlen($val) >= $minlen) {
                    $result[$val] = $cnt;
                }
            }
        } elseif (!is_null($key)) {
            $metaname = $this->cleanName($key);
            $index = $this->getIndex($metaname.'_i', '');
            $val_idx = array();
            foreach ($index as $wid => $line) {
                $freq = $this->countTuples($line);
                if ($freq >= $min && (!$max || $freq <= $max)) {
                    $val_idx[$wid] = $freq;
                }
            }
            if (!empty($val_idx)) {
                $words = $this->getIndex($metaname.'_w', '');
                foreach ($val_idx as $wid => $freq) {
                    if (strlen($words[$wid]) >= $minlen) {
                        $result[$words[$wid]] = $freq;
                    }
                }
            }
        } else {
            $PagewordIndex = PagewordIndex::getInstance();
            $lengths = $PagewordIndex->listIndexLengths();
            foreach ($lengths as $length) {
                if ($length < $minlen) continue;
                $index = $this->getIndex('i', $length);
                $words = null;
                foreach ($index as $wid => $line) {
                    $freq = $this->countTuples($line);
                    if ($freq >= $min && (!$max || $freq <= $max)) {
                        if ($words === null) {
                            $words = $this->getIndex('w', $length);
                        }
                        $result[$words[$wid]] = $freq;
                    }
                }
            }
        }

        arsort($result);
        return $result;
    }
}
