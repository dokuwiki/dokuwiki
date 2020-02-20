<?php

namespace dokuwiki\Search;

use dokuwiki\Extension\Event;
use dokuwiki\Search\FulltextIndex;
use dokuwiki\Search\MetadataIndex;

// Version tag used to force rebuild on upgrade
const INDEXER_VERSION = 8;

/**
 * Class DokuWiki Indexer (Singleton)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class Indexer extends AbstractIndex
{
    /** @var Indexer $instance */
    protected static $instance = null;

    /**
     * Get new or existing singleton instance of the Indexer
     *
     * @return Indexer
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Dispatch Indexing request for the page, called by TaskRunner::runIndexer()
     *
     * @param string        $page   name of the page to index
     * @param bool          $verbose    print status messages
     * @param bool          $force  force reindexing even when the index is up to date
     * @return bool  If the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     */
    public function dispatch($page, $verbose = false, $force = false)
    {
        // check if page was deleted but is still in the index
        if (!page_exists($page)) {
            return $this->deletePage($page, $verbose, $force);
        }

        // update search index
        return $this->addPage($page, $verbose, $force);
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
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     */
    public function addPage($page, $verbose = false, $force = false)
    {
        // check if indexing needed for the existing page (full text and/or metadata indexing)
        $idxtag = metaFN($page,'.indexed');
        if (!$force && file_exists($idxtag)) {
            if (trim(io_readFile($idxtag)) == $this->getVersion()) {
                $last = @filemtime($idxtag);
                if ($last > @filemtime(wikiFN($page))) {
                    if ($verbose) dbglog("Indexer: index for {$page} up to date");
                    return true;
                }
            }
        }

        // register the page to the page.idx
        $pid = $this->getPID($page);
        if ($pid === false) {
            if ($verbose) dbglog("Indexer: getting the PID failed for {$page}");
            trigger_error("Failed to get PID for {$page}", E_USER_ERROR);
            return false;
        }

        // prepare metadata indexing
        $metadata = array();
        $metadata['title'] = p_get_metadata($page, 'title', METADATA_RENDER_UNLIMITED);

        $references = p_get_metadata($page, 'relation references', METADATA_RENDER_UNLIMITED);
        $metadata['relation_references'] = ($references !== null) ?
                array_keys($references) : array();

        $media = p_get_metadata($page, 'relation media', METADATA_RENDER_UNLIMITED);
        $metadata['relation_media'] = ($media !== null) ?
                array_keys($media) : array();

        // check if full text indexing allowed
        $indexenabled = p_get_metadata($page, 'internal index', METADATA_RENDER_UNLIMITED);
        if ($indexenabled !== false) $indexenabled = true;
        $metadata['internal_index'] = $indexenabled;

        $body = '';
        $data = compact('page', 'body', 'metadata', 'pid');
        $event = new Event('INDEXER_PAGE_ADD', $data);
        if ($event->advise_before()) $data['body'] = $data['body'].' '.rawWiki($page);
        $event->advise_after();
        unset($event);
        extract($data);
        $indexenabled = $metadata['internal_index'];
        unset($metadata['internal_index']);

        // Access to Metadata Index
        $MetadataIndex = MetadataIndex::getInstance();
        $result = $MetadataIndex->addMetaKeys($page, $metadata);
        if ($verbose) dbglog("Indexer: addMetaKeys({$page}) ".($result ? 'done' : 'failed'));
        if (!$result) {
            return false;
        }

        // Access to Fulltext Index
        $FulltextIndex = FulltextIndex::getInstance();
        if ($indexenabled) {
            $result = $FulltextIndex->addPagewords($page, $body);
            if ($verbose) dbglog("Indexer: addPageWords({$page}) ".($result ? 'done' : 'failed'));
            if (!$result) {
                return false;
            }
        } else {
            if ($verbose) dbglog("Indexer: full text indexing disabled for {$page}");
            // ensure the page content deleted from the Fulltext index
            $result = $FulltextIndex->deletePageWords($page);
            if ($verbose) dbglog("Indexer: deletePageWords({$page}) ".($result ? 'done' : 'failed'));
            if (!$result) {
                return false;
            }
        }

        // update index tag file
        io_saveFile($idxtag, $this->getVersion());
        if ($verbose) dbglog("Indexer: finished");

        return $result;
    }

    /**
     * Remove a page from the index, erases entries in all known indexes
     *
     * Locking is handled internally.
     *
     * @param string        $page   name of the page to index
     * @param bool          $verbose    print status messages
     * @param bool          $force  force reindexing even when the index is up to date
     * @return bool  If the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Satoshi Sahara <sahara.satoshi@gmail.com>
     */
    public function deletePage($page, $verbose = false, $force = false)
    {
        $idxtag = metaFN($page,'.indexed');
        if (!$force && !file_exists($idxtag)) {
            if ($verbose) dbglog("Indexer: {$page}.indexed file does not exist, ignoring");
            return true;
        }

        // remove obsoleted content from Fulltext index
        $FulltextIndex = FulltextIndex::getInstance();
        $result = $FulltextIndex->deletePageWords($page);
        if ($verbose) dbglog("Indexer: deletePageWords({$page}) ".($result ? 'done' : 'failed'));
        if (!$result) {
            return false;
        }

        // delete all keys of the page from metadata index
        $MetadataIndex = MetadataIndex::getInstance();
        $result = $MetadataIndex->deleteMetaKeys($page);
        if ($verbose) dbglog("Indexer: deleteMetaKeys({$page}) ".($result ? 'done' : 'failed'));
        if (!$result) {
            return false;
        }

        // mark the page as deleted in the page.idx
        $pid = $this->getPID($page);
        if ($pid !== false) {
            if (!$this->lock()) return false;
            $result = $this->saveIndexKey('page', '', $pid, self::INDEX_MARK_DELETED.$page);
            if ($verbose) dbglog("Indexer: update page.idx  ".($result ? 'done' : 'failed'));
            $this->unlock();
        } else {
            if ($verbose) dbglog("Indexer: {$page} not found in the page.idx, ignoring");
            $result = true;
        }

        unset(static::$pidCache[$pid]);
        @unlink($idxtag);
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
        $index = $this->getIndex('page', '');
        // check if oldpage found in page.idx
        $oldPid = array_search($oldpage, $index, true);
        if ($oldPid === false) return false;

        // check if newpage found in page.idx
        $newPid = array_search($newpage, $index, true);
        if ($newPid !== false) {
            $result = $this->deletePage($newpage);
            if (!$result) return false;
            // Note: $index is no longer valid after deletePage()!
            unset($index);
        }

        // update page.idx
        if (!$this->lock()) return false;
        $result = $this->saveIndexKey('page', '', $oldPid, $newpage);
        $this->unlock();

        // reset the pid cache
        $this->resetPIDCache();

        return $result;
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

        // clear Fulltext Index
        $FulltextIndex = FulltextIndex::getInstance();
        $FulltextIndex->clear(false);

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
    public function histogram($key = null, $min = 1, $max = 0, $minlen = 3)
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
            $FulltextIndex = FulltextIndex::getInstance();
            $lengths = $FulltextIndex->listIndexLengths();
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
