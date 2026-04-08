<?php

namespace dokuwiki\Search\Collection;

/**
 * Search a collection for one or more terms with wildcards
 *
 * Works with any AbstractCollection (Frequency, Lookup, Direct) and handles both
 * split-by-length and non-split index layouts transparently.
 *
 * Use addTerm() to register search terms (with optional wildcards), then call execute().
 * Set caseInsensitive() on the search or on individual terms for case-insensitive matching.
 */
class CollectionSearch
{
    /** @var Term[] all terms indexed by original term name */
    protected array $allTerms = [];

    /** @var array<int, Term[]> references to terms indexed by group (length for split, 0 for non-split) */
    protected array $groupedTerms = [];

    /** @var AbstractCollection The collection this search works on */
    protected AbstractCollection $collection;

    /** @var ?int the maximum token index suffix as currently indexed */
    protected ?int $max = null;

    /** @var bool default case sensitivity for new terms */
    protected bool $defaultCaseInsensitive = false;

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
     * Enable case-insensitive matching for all subsequently added terms
     *
     * @return static
     */
    public function caseInsensitive(): static
    {
        $this->defaultCaseInsensitive = true;
        return $this;
    }

    /**
     * Add a term that will be looked up in the index later
     *
     * @param string $term the search term, may include * wildcards at start/end
     * @return Term the internal representation of the term, it will not be complete before the search has been executed
     */
    public function addTerm(string $term): Term
    {
        $term = new Term($term);

        if ($this->defaultCaseInsensitive) {
            $term->caseInsensitive();
        }

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
     * For each index group, scans the token index once testing all terms, then resolves
     * which entities have the matched tokens (via the frequency index). After all groups
     * are processed, entity IDs are batch-resolved to names via the entity index, and
     * each Term is populated with the final results: entity name → token name → frequency.
     *
     * @return Term[] All defined terms keyed by original term string
     */
    public function execute(): array
    {
        // Pass 1: per group, scan tokens and resolve frequencies
        $allEntityIds = [];
        $groupResults = [];
        foreach ($this->groupedTerms as $group => $terms) {
            $tokenIndex = $this->collection->getTokenIndex($group);
            if (!$tokenIndex->exists()) continue;

            // single-pass token scan for all terms in this group
            $tokenMatches = []; // [tokenId => [{term, token}, ...]]
            foreach ($tokenIndex as $tokenId => $tokenValue) {
                if ($tokenValue === '') continue;
                foreach ($terms as $term) {
                    if ($term->matches($tokenValue)) {
                        $tokenMatches[$tokenId][] = ['term' => $term, 'token' => $tokenValue];
                    }
                }
            }
            if ($tokenMatches === []) continue;

            // resolve which entities have these tokens
            $freqs = $this->collection->resolveTokenFrequencies($group, array_keys($tokenMatches));

            // collect entity IDs for batch name resolution
            foreach ($freqs as $entityFreqs) {
                foreach (array_keys($entityFreqs) as $entityId) {
                    $allEntityIds[$entityId] = true;
                }
            }

            $groupResults[] = ['matches' => $tokenMatches, 'freqs' => $freqs];
        }

        if ($allEntityIds === []) return $this->allTerms;

        // Batch resolve entity IDs to names (single sequential file read)
        $entityMap = $this->collection->getEntityIndex()->retrieveRows(array_keys($allEntityIds));

        // Pass 2: populate Terms with fully resolved data
        foreach ($groupResults as $data) {
            foreach ($data['freqs'] as $tokenId => $entityFreqs) {
                foreach ($data['matches'][$tokenId] as $match) {
                    foreach ($entityFreqs as $entityId => $freq) {
                        $entityName = $entityMap[$entityId] ?? '';
                        if ($entityName === '') continue;
                        $match['term']->addMatch($entityName, $match['token'], $freq);
                    }
                }
            }
        }

        return $this->allTerms;
    }
}
