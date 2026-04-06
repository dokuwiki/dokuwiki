<?php

namespace dokuwiki\Search;

use dokuwiki\Extension\Event;
use dokuwiki\Search\Collection\PageFulltextCollection;
use dokuwiki\Search\Collection\PageMetaCollection;
use dokuwiki\Search\Collection\PageTitleCollection;
use dokuwiki\Search\Exception\IndexAccessException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Exception\IndexWriteException;
use dokuwiki\Search\Index\FileIndex;
use dokuwiki\Search\Index\Lock;

// Version tag used to force rebuild on upgrade
const INDEXER_VERSION = 8;

/**
 * Class DokuWiki Indexer
 *
 * Manages the page search index by delegating to Collection classes.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class Indexer
{
    /** @var callable|null Logging callback, receives a string message */
    protected $logger;

    /**
     * Set a logging callback
     *
     * The callback receives a single string message. Use this to integrate
     * with different output mechanisms (TaskRunner echo, CLI output, Logger, etc.)
     *
     * @param callable $logger
     * @return static
     */
    public function setLogger(callable $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Send a message to the registered logger
     *
     * @param string $message
     */
    protected function log(string $message): void
    {
        if ($this->logger) ($this->logger)($message);
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
     * @return int|string
     */
    public function getVersion()
    {
        static $indexer_version = null;
        if ($indexer_version == null) {
            $version = INDEXER_VERSION;

            $data = ['dokuwiki' => $version];
            Event::createAndTrigger('INDEXER_VERSION_GET', $data, null, false);
            unset($data['dokuwiki']); // this needs to be first
            ksort($data);
            foreach ($data as $plugin => $vers) {
                $version .= '+' . $plugin . '=' . $vers;
            }
            $indexer_version = $version;
        }
        return $indexer_version;
    }

    /**
     * Return a list of all indexed pages
     *
     * @param bool $existsFilter only return pages that exist on disk
     * @return string[] list of page names (keys are the RIDs in the page index)
     */
    public function getAllPages(bool $existsFilter = false): array
    {
        $pageIndex = new Index\MemoryIndex('page');
        return array_filter(
            iterator_to_array($pageIndex),
            static fn($v) => $v !== '' && (!$existsFilter || page_exists($v, '', false))
        );
    }

    /**
     * Check if a page needs (re-)indexing
     *
     * @param string $page
     * @param bool $force
     * @return bool true if indexing is needed
     */
    public function needsIndexing(string $page, bool $force = false): bool
    {
        $idxtag = metaFN($page, '.indexed');
        if ($force || !file_exists($idxtag)) return true;

        if (trim(io_readFile($idxtag)) != $this->getVersion()) return true;

        $last = @filemtime($idxtag);
        return $last <= @filemtime(wikiFN($page));
    }

    /**
     * Add/update the search index for a page
     *
     * Locking is handled internally.
     *
     * @param string $page The page to index
     * @param bool $force force reindexing even when the index is up to date
     *
     * @throws IndexAccessException
     * @throws IndexLockException
     * @throws IndexWriteException
     */
    public function addPage(string $page, bool $force = false): void
    {
        if (!$this->needsIndexing($page, $force)) {
            $this->log("Indexer: index for {$page} up to date");
            return;
        }

        // create shared writable page index early so we can resolve the PID for plugins
        $pageIndex = new FileIndex('page', '', true);

        // prepare event data
        $data = [
            'page' => $page,
            'body' => '',
            'metadata' => [
                'title' => p_get_metadata($page, 'title', METADATA_RENDER_UNLIMITED),
                'relation_references' => array_keys(
                    p_get_metadata($page, 'relation references', METADATA_RENDER_UNLIMITED) ?? []
                ),
                'relation_media' => array_keys(
                    p_get_metadata($page, 'relation media', METADATA_RENDER_UNLIMITED) ?? []
                ),
                'internal_index' => p_get_metadata($page, 'internal index', METADATA_RENDER_UNLIMITED) !== false,
            ],
            'pid' => $pageIndex->accessCachedValue($page),
        ];

        // let plugins modify the data
        $event = new Event('INDEXER_PAGE_ADD', $data);
        if ($event->advise_before()) {
            $data['body'] = $data['body'] . ' ' . rawWiki($data['page']);
        }
        $event->advise_after();
        unset($event);

        // index title
        (new PageTitleCollection($pageIndex))->lock()
            ->addEntity($data['page'], [$data['metadata']['title']])->unlock();
        unset($data['metadata']['title']);

        // index fulltext
        if ($data['metadata']['internal_index']) {
            $words = Tokenizer::getWords($data['body']);
            (new PageFulltextCollection($pageIndex))->lock()->addEntity($data['page'], $words)->unlock();
        } else {
            $this->log("Indexer: full text indexing disabled for {$data['page']}");
            // clear any previously stored fulltext data
            (new PageFulltextCollection($pageIndex))->lock()->addEntity($data['page'], [])->unlock();
        }
        unset($data['metadata']['internal_index']);

        // index metadata keys
        foreach ($data['metadata'] as $key => $values) {
            if (!is_array($values)) {
                $values = ($values !== null && $values !== '') ? [$values] : [];
            }
            (new PageMetaCollection($key, $pageIndex))->lock()->addEntity($data['page'], $values)->unlock();
        }

        // update metadata registry
        $this->updateMetadataRegistry(array_keys($data['metadata']));

        // update index tag file
        io_saveFile(metaFN($data['page'], '.indexed'), $this->getVersion());
        $this->log("Indexer: finished indexing {$data['page']}");
    }

    /**
     * Remove a page from the index
     *
     * Clears the page's data from all collections. The entity persists in page.idx.
     *
     * @param string $page The page to remove
     * @param bool $force force deletion even when no .indexed tag exists
     *
     * @throws IndexAccessException
     * @throws IndexLockException
     * @throws IndexWriteException
     */
    public function deletePage(string $page, bool $force = false): void
    {
        $idxtag = metaFN($page, '.indexed');
        if (!$force && !file_exists($idxtag)) {
            $this->log("Indexer: {$page}.indexed file does not exist, ignoring");
            return;
        }

        $pageIndex = new FileIndex('page', '', true);

        (new PageTitleCollection($pageIndex))->lock()->addEntity($page, [])->unlock();
        (new PageFulltextCollection($pageIndex))->lock()->addEntity($page, [])->unlock();

        foreach ($this->getMetadataRegistryKeys() as $key) {
            (new PageMetaCollection($key, $pageIndex))->lock()->addEntity($page, [])->unlock();
        }

        $this->log("Indexer: deleted {$page} from index");
        @unlink($idxtag);
    }

    /**
     * Rename a page in the search index
     *
     * The page must already have been moved on disk before calling this.
     * Clears the old page's data and re-indexes under the new name.
     *
     * @param string $oldpage The old page name
     * @param string $newpage The new page name
     *
     * @throws IndexAccessException
     * @throws IndexLockException
     * @throws IndexWriteException
     */
    public function renamePage(string $oldpage, string $newpage): void
    {
        $this->deletePage($oldpage, true);
        $this->addPage($newpage, true);
    }

    /**
     * Clear all page indexes
     */
    public function clear(): void
    {
        global $conf;

        Lock::acquire('page');

        // clear metadata indexes
        foreach ($this->getMetadataRegistryKeys() as $key) {
            $clean = PageMetaCollection::cleanName($key);
            @unlink($conf['indexdir'] . '/' . $clean . '_w.idx');
            @unlink($conf['indexdir'] . '/' . $clean . '_i.idx');
            @unlink($conf['indexdir'] . '/' . $clean . '_p.idx');
        }

        // clear fulltext indexes
        $files = glob($conf['indexdir'] . '/i*.idx');
        if ($files) foreach ($files as $f) @unlink($f);
        $files = glob($conf['indexdir'] . '/w*.idx');
        if ($files) foreach ($files as $f) @unlink($f);

        @unlink($conf['indexdir'] . '/pageword.idx');
        @unlink($conf['indexdir'] . '/lengths.idx');

        // clear title and page indexes
        @unlink($conf['indexdir'] . '/title.idx');
        @unlink($conf['indexdir'] . '/page.idx');
        @unlink($conf['indexdir'] . '/metadata.idx');

        Lock::release('page');
    }

    /**
     * Get the list of known metadata keys from the metadata registry
     *
     * @return string[] list of metadata key names
     */
    protected function getMetadataRegistryKeys(): array
    {
        global $conf;
        $fn = $conf['indexdir'] . '/metadata.idx';
        if (!file_exists($fn)) return [];
        $keys = file($fn, FILE_IGNORE_NEW_LINES);
        return $keys ?: [];
    }

    /**
     * Update the metadata registry with new keys
     *
     * @param string[] $keys metadata key names to ensure are registered
     */
    protected function updateMetadataRegistry(array $keys): void
    {
        global $conf;
        $fn = $conf['indexdir'] . '/metadata.idx';
        $existing = file_exists($fn) ? file($fn, FILE_IGNORE_NEW_LINES) : [];
        if (!$existing) $existing = [];

        $added = false;
        foreach ($keys as $key) {
            if (!in_array($key, $existing)) {
                $existing[] = $key;
                $added = true;
            }
        }

        if ($added) {
            io_saveFile($fn, implode("\n", $existing) . "\n");
        }
    }
}
