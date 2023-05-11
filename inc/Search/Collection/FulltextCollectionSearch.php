<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Index\TupleOps;

/**
 * Search a collection for one or more terms with wildcards
 *
 * Note that this does not implement the Search syntax. Instead it provides an efficient way to search a collection
 * for all the terms the query parser has identified. The results can then be used assemble an actual search result
 * matching the intends of the query syntax.
 *
 * @todo decide which parts to move into an abstract base class
 */
class FulltextCollectionSearch
{

    /** @var Term[] all terms indexed by original term name */
    protected $allTerms = [];

    /** @var array references to terms indexed by term length */
    protected $lengthTerms = [];

    /** @var array a list of entities that match [entityID => entityName] */
    protected $entities = [];

    /** @var FulltextCollection The collection this search works on */
    protected $collection;

    /** @var int the maximum token length as currently indexed */
    protected $max = null;

    /** @var array [entityName => frequency] */
    protected $entityFrequencySums = [];

    /**
     * Initialize a search on the given collection
     *
     * @param FulltextCollection $collection
     */
    public function __construct(FulltextCollection $collection)
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
    public function addTerm($term)
    {
        $term = new Term($term);

        // we keep all terms in an array
        $this->allTerms[$term->getOriginal()] = $term;

        // for wildcards, we need to find tokens from all indexes equal or larger than the term length
        if ($term->getWildcard()) {
            // if a wildcard term is added we will need the maximum token index length
            if ($this->max === null) {
                $this->max = $this->collection->getTokenIndexMaximum();
            }
            $max = $this->max;
        } else {
            $max = $term->getLength();
        }

        // add the term to our length based list
        for ($i = $term->getLength(); $i <= $max; $i++) {
            if (!isset($this->lengthTerms[$i])) {
                $this->lengthTerms[$i] = [];
            }
            $this->lengthTerms[$i][] = $term;
        }

        return $term;
    }


    /**
     * Execute the search
     *
     * @return Term[] All defined terms. Use their methods to access the results
     */
    public function execute()
    {
        $this->findTokens();
        $this->findFrequencies();
        $this->findEntities();

        return $this->allTerms;
    }

    /**
     * Get the entities that have the term
     *
     * @return array [entityID => entityName, ...]
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Look up the matching tokens for all set terms
     *
     * Because a term can contain wildcards, many tokens may actually match that term.
     *
     * Updates the token field of the terms to [tid => token, ...]
     * @return void
     */
    protected function findTokens()
    {
        // look up each term in the tokenIndex
        // because of wildcards, terms may match many tokens
        foreach ($this->lengthTerms as $len => $terms) {
            $tokenIndex = $this->collection->getTokenIndex($len);
            if (!$tokenIndex->exists()) continue;
            foreach ($terms as $term) {
                /** @var Term $term */
                $term->addTokens($len, $tokenIndex->search('/^' . $term->getQuoted() . '$/'));
            }
        }
    }

    protected function findFrequencies()
    {
        // look up the frequencies for all found terms
        foreach ($this->lengthTerms as $len => $terms) {
            $frequencyIndex = $this->collection->getFrequencyIndex($len);
            if (!$frequencyIndex->exists()) continue;
            foreach ($terms as $term) {
                /** @var Term $term */
                $tokenLines = $frequencyIndex->retrieveRows($term->getTokenIDsByLength($len));
                foreach ($tokenLines as $line) {
                    $frequencies = TupleOps::parseTuples($line);
                    foreach ($frequencies as $entityID => $frequency) {
                        // add the frequency to the term
                        $term->addEntityFrequency($entityID, $frequency);

                        // remember the entityID
                        $this->entities[$entityID] = ''; // names are added later
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
    protected function findEntities()
    {
        $entityIndex = $this->collection->getEntityIndex();
        $this->entities = $entityIndex->retrieveRows(array_keys($this->entities));

        // give terms access to resolved entity names
        foreach ($this->allTerms as $term) {
            $term->resolveEntities($this->entities);
        }
    }

}
