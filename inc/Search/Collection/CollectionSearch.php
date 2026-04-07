<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Index\AbstractIndex;

/**
 * Search a collection for one or more terms with wildcards
 *
 * Works with any AbstractCollection (Frequency, Lookup, Direct) and handles both
 * split-by-length and non-split index layouts transparently.
 *
 * Provides two APIs:
 * - addTerm()/execute(): For fulltext-style search with Term objects and min-length validation
 * - lookup(): For metadata-style search with exact/wildcard/callback matching, no length restrictions
 */
class CollectionSearch
{
    /** @var Term[] all terms indexed by original term name */
    protected array $allTerms = [];

    /** @var array<int, Term[]> references to terms indexed by group (length for split, 0 for non-split) */
    protected array $groupedTerms = [];

    /** @var array<int, string> a list of entities that match [entityID => entityName] */
    protected array $entities = [];

    /** @var AbstractCollection The collection this search works on */
    protected AbstractCollection $collection;

    /** @var ?int the maximum token index suffix as currently indexed */
    protected ?int $max = null;

    /**
     * Initialize a search on the given collection
     *
     * @param AbstractCollection $collection
     */
    public function __construct(AbstractCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Add a term that will be looked up in the index later
     *
     * @param string $term
     * @return Term the internal representation of the term, it will not be complete before the search has been executed
     * @throws SearchException if the given term was too short or otherwise invalid
     */
    public function addTerm(string $term): Term
    {
        $term = new Term($term);

        // we keep all terms in an array
        $this->allTerms[$term->getOriginal()] = $term;

        if ($this->collection->isSplitByLength()) {
            // for wildcards, we need to find tokens from all indexes equal or larger than the term length
            if ($term->getWildcard()) {
                if ($this->max === null) {
                    $this->max = $this->collection->getTokenIndexMaximum();
                }
                $max = $this->max;
            } else {
                $max = $term->getLength();
            }

            for ($i = $term->getLength(); $i <= $max; $i++) {
                $this->groupedTerms[$i][] = $term;
            }
        } else {
            // non-split: all terms go into a single group
            $this->groupedTerms[0][] = $term;
        }

        return $term;
    }

    /**
     * Execute the search
     *
     * @return Term[] All defined terms. Use their methods to access the results
     */
    public function execute(): array
    {
        $this->findTokens();
        $this->findFrequencies();
        $this->findEntities();

        return $this->allTerms;
    }

    /**
     * Get the entities that have the term
     *
     * @return array<int, string> [entityID => entityName, ...]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * Search for values in the collection's token index
     *
     * A simpler API for metadata-style lookups without Term objects or min-length restrictions.
     * Supports exact match, wildcard (*), and callback matching.
     *
     * @param string|string[] $values search values
     * @param callable|null $func comparison function: fn($searchValue, $indexWord) => bool
     * @return array [value => [entityName, ...], ...]
     */
    public function lookup(string|array $values, ?callable $func = null): array
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        $result = array_fill_keys($values, []);

        // determine which groups to search
        $max = $this->collection->isSplitByLength() ? $this->collection->getTokenIndexMaximum() : 0;
        $groups = $this->collection->isSplitByLength()
            ? ($max > 0 ? range(1, $max) : [])
            : [0];

        // find matching token IDs across all groups
        $allMatches = []; // [group => [tokenId => [value, ...], ...]]
        $allEntityIds = [];

        foreach ($groups as $group) {
            $tokenIndex = $this->collection->getTokenIndex($group);
            if (!$tokenIndex->exists()) continue;

            $matches = $this->findMatchingTokens($tokenIndex, $values, $func);
            if (empty($matches)) continue;

            // resolve token IDs to entity frequencies
            $tokenFreqs = $this->collection->resolveTokenFrequencies($group, array_keys($matches));
            foreach ($tokenFreqs as $tokenId => $frequencies) {
                foreach ($frequencies as $entityId => $freq) {
                    $allEntityIds[$entityId] = true;
                }
            }

            $allMatches[$group] = ['matches' => $matches, 'freqs' => $tokenFreqs];
        }

        if (empty($allEntityIds)) return $result;

        // resolve entity IDs to names
        $entityIndex = $this->collection->getEntityIndex();
        $entityNames = $entityIndex->retrieveRows(array_keys($allEntityIds));

        // assemble results
        foreach ($allMatches as $group => $data) {
            foreach ($data['matches'] as $tokenId => $valList) {
                $pages = [];
                if (isset($data['freqs'][$tokenId])) {
                    foreach (array_keys($data['freqs'][$tokenId]) as $entityId) {
                        if (isset($entityNames[$entityId]) && $entityNames[$entityId] !== '') {
                            $pages[] = $entityNames[$entityId];
                        }
                    }
                }
                foreach ($valList as $val) {
                    $result[$val] = array_merge($result[$val], $pages);
                }
            }
        }

        return $result;
    }

    /**
     * Find token IDs matching the given values using exact, wildcard, or callback matching
     *
     * @param AbstractIndex $tokenIndex
     * @param string[] $values
     * @param callable|null $func
     * @return array [tokenId => [value, ...], ...] matching token IDs with the values they matched
     */
    protected function findMatchingTokens(AbstractIndex $tokenIndex, array $values, ?callable $func): array
    {
        $matches = [];

        if ($func !== null) {
            // callback matching: iterate all tokens
            foreach ($tokenIndex as $tokenId => $word) {
                if ($word === '') continue;
                foreach ($values as $val) {
                    if (call_user_func($func, $val, $word)) {
                        $matches[$tokenId][] = $val;
                    }
                }
            }
        } else {
            foreach ($values as $val) {
                $xval = $val;
                $caret = '^';
                $dollar = '$';
                if (substr($xval, 0, 1) === '*') {
                    $xval = substr($xval, 1);
                    $caret = '';
                }
                if (substr($xval, -1, 1) === '*') {
                    $xval = substr($xval, 0, -1);
                    $dollar = '';
                }
                if (!$caret || !$dollar) {
                    // wildcard matching
                    $re = '/' . $caret . preg_quote($xval, '/') . $dollar . '/';
                    foreach ($tokenIndex->search($re) as $tokenId => $word) {
                        $matches[$tokenId][] = $val;
                    }
                } else {
                    // exact matching
                    $tokenId = $tokenIndex->getRowID($val);
                    if ($tokenId !== null) {
                        $matches[$tokenId][] = $val;
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Look up the matching tokens for all set terms
     *
     * @return void
     */
    protected function findTokens(): void
    {
        foreach ($this->groupedTerms as $group => $terms) {
            $tokenIndex = $this->collection->getTokenIndex($group);
            if (!$tokenIndex->exists()) continue;
            foreach ($terms as $term) {
                $term->addTokens($group, $tokenIndex->search('/^' . $term->getQuoted() . '$/'));
            }
        }
    }

    /**
     * Look up the entity frequencies for all tokens found by findTokens
     *
     * @return void
     */
    protected function findFrequencies(): void
    {
        foreach ($this->groupedTerms as $group => $terms) {
            foreach ($terms as $term) {
                $tokenIds = $term->getTokenIDsByGroup($group);
                if (empty($tokenIds)) continue;

                $tokenFreqs = $this->collection->resolveTokenFrequencies($group, $tokenIds);
                foreach ($tokenFreqs as $tokenId => $frequencies) {
                    foreach ($frequencies as $entityID => $frequency) {
                        $term->addEntityFrequency($entityID, $frequency);
                        $this->entities[$entityID] = '';
                    }
                }
            }
        }
    }

    /**
     * Lookup the actual names of found entities
     *
     * @return void
     */
    protected function findEntities(): void
    {
        $entityIndex = $this->collection->getEntityIndex();
        $this->entities = $entityIndex->retrieveRows(array_keys($this->entities));

        foreach ($this->allTerms as $term) {
            $term->resolveEntities($this->entities);
        }
    }
}
