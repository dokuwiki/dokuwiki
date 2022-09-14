<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Tokenizer;

/**
 * Simple object to hold data during a collection search
 */
class FulltextCollectionResult
{

    /** @var array all terms indexed by orginal term name */
    public $allTerms = [];

    /** @var array references to exact terms indexed by term length */
    public $exactTerms = [];

    /** @var array references to wildcard terms indexed by term length */
    public $wildcardTerms = [];

    /**
     * Add a term that will be looked up in the index later
     *
     * @param string $term
     * @return void
     */
    public function addTerm($term)
    {
        $length = FulltextCollection::tokenLength($term);
        $quoted = preg_quote_cb(trim($term, '*'));

        if (substr($term, 0, 1) === '*') {
            $quoted = ".*$quoted";
            $length -= 1;
        }

        if (substr($term, -1, 1) === '*') {
            $quoted = "$quoted.*";
            $length -= 1;
        }

        // ignore terms that are too short, with an exception on numbers
        if ($length < Tokenizer::getMinWordLength() && !is_numeric($term)) {
            return;
        }

        $this->allTerms[$term] = [
            'original' => $term,
            'wildcard' => $quoted,
            'length' => $length,
            'tids' => [],
        ];

        if ($term == $quoted) {
            if (!isset($this->exactTerms[$length])) {
                $this->exactTerms[$length] = [];
            }
            $this->exactTerms[$length][] = &$this->allTerms[$term];
        } else {
            if (!isset($this->wildcardTerms[$length])) {
                $this->wildcardTerms[$length] = [];
            }
            $this->wildcardTerms[$length][] = &$this->allTerms[$term];
        }
    }
}
