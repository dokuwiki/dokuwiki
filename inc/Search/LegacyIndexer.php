<?php

namespace dokuwiki\Search;

use dokuwiki\Debug\DebugHelper;
use dokuwiki\Search\Collection\AbstractCollection;
use dokuwiki\Search\Collection\CollectionSearch;
use dokuwiki\Search\Collection\PageFulltextCollection;
use dokuwiki\Search\Collection\PageMetaCollection;
use dokuwiki\Search\Collection\PageTitleCollection;
use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Index\FileIndex;
use dokuwiki\Search\Index\TupleOps;

/**
 * Backward-compatible wrapper around {@see Indexer}
 *
 * The refactored {@see Indexer} reports failures by throwing
 * {@see SearchException} subclasses. Plugins written against the legacy
 * Doku_Indexer API expect the four mutating methods (addPage, deletePage,
 * renamePage, clear) to return `true` on success or a string error message
 * on failure. This class wraps an {@see Indexer} instance and restores that
 * contract for those four methods. It also hosts the legacy helpers
 * (lookupKey, getPages, addMetaKeys, renameMetaValue, getPID, lookup) that
 * used to live on Indexer itself.
 *
 * It is returned by the deprecated {@see ::idx_get_indexer()} helper, which
 * is the entry point most plugins use to obtain an indexer instance. New
 * code should instantiate {@see Indexer} directly and handle
 * {@see SearchException} via try/catch.
 *
 * Composition (not inheritance) is used because PHP does not allow
 * overriding a `void` return type with `bool|string`.
 *
 * @deprecated 2026-04-07 use {@see Indexer} directly with try/catch
 *
 * @method string|int getVersion()
 * @method string[] getAllPages(bool $existsFilter = false)
 * @method string[] getPages(?string $key = null)
 * @method bool needsIndexing(string $page, bool $force = false)
 * @method void checkIntegrity()
 * @method bool isIndexEmpty()
 */
class LegacyIndexer
{
    protected Indexer $indexer;

    public function __construct(?Indexer $indexer = null)
    {
        $this->indexer = $indexer ?? new Indexer();
    }

    /**
     * Forward any other call (getVersion, getAllPages, getPages, needsIndexing,
     * checkIntegrity, isIndexEmpty, ...) to the wrapped indexer.
     *
     * @deprecated 2026-04-07 call the same method on {@see Indexer} directly
     */
    public function __call(string $name, array $args): mixed
    {
        DebugHelper::dbgDeprecatedFunction(Indexer::class . '::' . $name . '()');
        return $this->indexer->$name(...$args);
    }

    /**
     * @return bool|string true if work was done, false if there was nothing to do,
     *                     error message string on failure
     *
     * @deprecated 2026-04-07 use {@see Indexer::addPage()} with try/catch instead
     */
    public function addPage(string $page, bool $force = false): bool|string
    {
        DebugHelper::dbgDeprecatedFunction(Indexer::class . '::addPage()');
        try {
            return $this->indexer->addPage($page, $force);
        } catch (SearchException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string true if work was done, false if there was nothing to do,
     *                     error message string on failure
     *
     * @deprecated 2026-04-07 use {@see Indexer::deletePage()} with try/catch instead
     */
    public function deletePage(string $page, bool $force = false): bool|string
    {
        DebugHelper::dbgDeprecatedFunction(Indexer::class . '::deletePage()');
        try {
            return $this->indexer->deletePage($page, $force);
        } catch (SearchException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string true if work was done, false if there was nothing to do,
     *                     error message string on failure
     *
     * @deprecated 2026-04-07 use {@see Indexer::renamePage()} with try/catch instead
     */
    public function renamePage(string $oldpage, string $newpage): bool|string
    {
        DebugHelper::dbgDeprecatedFunction(Indexer::class . '::renamePage()');
        try {
            $result = $this->indexer->renamePage($oldpage, $newpage);
            // a false result for differing names means the old page was not in the
            // index; restore the legacy error message that callers expect here
            if ($result === false && $oldpage !== $newpage) {
                return 'page is not in index';
            }
            return $result;
        } catch (SearchException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return true|string true on success, error message on failure
     *
     * @deprecated 2026-04-07 use {@see Indexer::clear()} with try/catch instead
     */
    public function clear(): bool|string
    {
        DebugHelper::dbgDeprecatedFunction(Indexer::class . '::clear()');
        try {
            $this->indexer->clear();
            return true;
        } catch (SearchException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Find pages containing a metadata value
     *
     * @param string $key metadata key name
     * @param string|string[] $value search term(s)
     * @param callable|null $func ignored, kept for backward compatibility
     * @return array
     *
     * @deprecated 2026-04-07 use MetadataSearch::lookupKey() instead
     */
    public function lookupKey($key, &$value, $func = null)
    {
        DebugHelper::dbgDeprecatedFunction(MetadataSearch::class . '::lookupKey()');
        return (new MetadataSearch())->lookupKey($key, $value);
    }

    /**
     * Add metadata values for a page
     *
     * @param string $page page name
     * @param string $key metadata key name
     * @param string|string[]|null $value value(s) to add
     * @return bool
     *
     * @deprecated 2026-04-07 use Collection classes directly instead
     */
    public function addMetaKeys($page, $key, $value = null)
    {
        DebugHelper::dbgDeprecatedFunction('Collection classes');
        try {
            if ($key === 'title') {
                $collection = new PageTitleCollection();
            } else {
                $collection = new PageMetaCollection($key);
            }
            $values = is_array($value) ? $value : ($value !== null && $value !== '' ? [$value] : []);
            $collection->lock()->addEntity($page, $values)->unlock();
            $this->indexer->updateMetadataRegistry([$key]);
            return true;
        } catch (SearchException) {
            return false;
        }
    }

    /**
     * Rename a metadata value in the index
     *
     * @param string $key metadata key name
     * @param string $oldvalue old value
     * @param string $newvalue new value
     * @return bool
     *
     * @deprecated 2026-04-07 use Collection classes directly instead
     */
    public function renameMetaValue($key, $oldvalue, $newvalue)
    {
        DebugHelper::dbgDeprecatedFunction('Collection classes');
        try {
            $collection = new PageMetaCollection($key);
            $collection->lock();

            $tokenIndex = $collection->getTokenIndex();

            // find old value — search() is read-only, won't create entries
            $matches = $tokenIndex->search('/^' . preg_quote($oldvalue, '/') . '$/');
            if ($matches === []) {
                $collection->unlock();
                return true;
            }
            $oldid = array_key_first($matches);

            // check if new value already exists (read-only lookup)
            $newMatches = $tokenIndex->search('/^' . preg_quote($newvalue, '/') . '$/');

            if ($newMatches !== []) {
                // both values exist — merge frequency data from old to new
                $newid = array_key_first($newMatches);
                $freqIndex = $collection->getFrequencyIndex();
                $reverseIndex = $collection->getReverseIndex();
                $oldFreqLine = $freqIndex->retrieveRow($oldid);

                if ($oldFreqLine !== '') {
                    $newFreqLine = $freqIndex->retrieveRow($newid);
                    foreach (TupleOps::parseTuples($oldFreqLine) as $entityId => $count) {
                        $newFreqLine = TupleOps::updateTuple($newFreqLine, $entityId, $count);

                        // update reverse index: remove old token, add new
                        $reverseRow = $reverseIndex->retrieveRow((int)$entityId);
                        $keyline = explode(':', $reverseRow);
                        $keyline = array_diff($keyline, [(string)$oldid]);
                        if (!in_array((string)$newid, $keyline)) {
                            $keyline[] = $newid;
                        }
                        $reverseIndex->changeRow(
                            (int)$entityId,
                            implode(':', array_filter($keyline, fn($v) => $v !== ''))
                        );
                    }
                    $freqIndex->changeRow($oldid, '');
                    $freqIndex->changeRow($newid, $newFreqLine);
                }
            } else {
                // new value doesn't exist — simple rename
                $tokenIndex->changeRow($oldid, $newvalue);
            }

            $collection->unlock();
            return true;
        } catch (SearchException) {
            return false;
        }
    }

    /**
     * Get the page ID for a page name
     *
     * @param string $page page name
     * @return int|false
     *
     * @deprecated 2026-04-07 use FileIndex directly instead
     */
    public function getPID($page)
    {
        DebugHelper::dbgDeprecatedFunction(FileIndex::class);
        try {
            return (new FileIndex('page', '', true))->accessCachedValue($page);
        } catch (SearchException) {
            return false;
        }
    }

    /**
     * Find tokens in the fulltext index
     *
     * @param array $tokens list of words to search for
     * @return array list of pages found [word => [page => count, ...]]
     *
     * @deprecated 2026-04-07 use CollectionSearch on PageFulltextCollection instead
     */
    public function lookup($tokens)
    {
        DebugHelper::dbgDeprecatedFunction(CollectionSearch::class);
        $collection = new PageFulltextCollection();
        $search = new CollectionSearch($collection);
        $termMap = [];
        foreach ($tokens as $token) {
            if (!Tokenizer::isValidSearchTerm($token)) continue;
            $term = $search->addTerm($token);
            $termMap[$token] = $term;
        }

        if ($termMap === []) return [];
        $search->execute();

        $result = [];
        foreach ($termMap as $word => $term) {
            $freqs = $term->getEntityFrequencies();
            // filter to only existing pages
            $filtered = array_filter($freqs, fn($page) => page_exists($page, '', false), ARRAY_FILTER_USE_KEY);
            $result[$word] = $filtered;
        }
        return $result;
    }

    /**
     * Build a frequency histogram of index terms (tag clouds, word lists)
     *
     * @param int $min minimum frequency
     * @param int $max maximum frequency, 0 for no upper limit
     * @param int $minlen minimum term length
     * @param string|null $key null for the fulltext word index, 'title' for the
     *     page title index, or a metadata key name for that metadata index
     * @return array<string, int> term => frequency, ordered by frequency descending
     *
     * @deprecated 2026-04-07 call histogram() on the matching Collection instead
     */
    public function histogram($min = 1, $max = 0, $minlen = 3, $key = null)
    {
        DebugHelper::dbgDeprecatedFunction(AbstractCollection::class . '::histogram()');
        if ($key === 'title') {
            $collection = new PageTitleCollection();
        } elseif ($key !== null) {
            $collection = new PageMetaCollection($key);
        } else {
            $collection = new PageFulltextCollection();
        }
        return $collection->histogram((int)$min, (int)$max, (int)$minlen);
    }
}
