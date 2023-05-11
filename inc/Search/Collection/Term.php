<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Tokenizer;

/**
 * Represents a term that is searched on a frequency based index
 *
 * A term can contain wildcards and thus may refer to various tokens of different lengths.
 *
 * @fixme add standalone tests for this class
 */
class Term
{

    const WILDCARD_NONE = 0;
    const WILDCARD_START = 1;
    const WILDCARD_END = 2;

    /** @var string the original term including wildcard chars */
    protected $original;

    /** @var string the base of the term without wildcard chars FIXME */
    protected $base;

    /** @var string the quoted term to be used in a regular expression */
    protected $quoted;

    /** @var int the length of the base term (not counting wildcards) */
    protected $length;

    /** @var int The type of wildcards */
    protected $wildcard;

    /** @var array The matching tokens for this term [length => [tokenID => tokenName, ...], ...] */
    protected $tokens;

    /** @var array The entity frequencies this term matches (aggregated over all tokens) [entity => frequency] */
    protected $frequencies;

    /**
     * @throws SearchException
     */
    public function __construct($term)
    {
        $this->original = $term;
        $this->base = trim($term, '*');
        $this->quoted = preg_quote_cb($this->base);
        $this->wildcard = self::WILDCARD_NONE;
        $this->length = Tokenizer::tokenLength($this->base);

        // handle wildcard
        if (substr($term, 0, 1) === '*') {
            $this->quoted = '.*' . $this->quoted;
            $this->wildcard += self::WILDCARD_START;
        }

        if (substr($term, -1, 1) === '*') {
            $this->quoted = $this->quoted . '.*';
            $this->wildcard += self::WILDCARD_END;
        }

        // ignore terms that are too short, with an exception on numbers
        if ($this->length === 0 || ($this->length < Tokenizer::getMinWordLength() && !is_numeric($term))) {
            throw new SearchException('Too short term');
        }
    }

    /**
     * @return string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @return string
     */
    public function getQuoted()
    {
        return $this->quoted;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getWildcard()
    {
        return $this->wildcard;
    }

    /**
     * @return array [entity => frequency, ...]
     */
    public function getEntityFrequencies()
    {
        return $this->frequencies;
    }

    /**
     * Add found tokens IDs of a specific length
     * @param int $length
     * @param array $tokens [tokenID => tokenName, ...]
     * @return void
     * @internal
     */
    public function addTokens($length, $tokens)
    {
        $this->tokens[$length] = [];
        foreach ($tokens as $tokenID => $tokenName) {
            $this->tokens[$length][$tokenID] = $tokenName;
        }
    }

    /**
     * Return all tokens that match the given term
     *
     * @return string
     */
    public function getTokens()
    {
        return array_merge(...array_map('array_values', array_values($this->tokens)));
    }

    /**
     * Return all token IDs of the given length
     *
     * @param $length
     * @return int[]
     */
    public function getTokenIDsByLength($length)
    {
        return isset($this->tokens[$length]) ? array_keys($this->tokens[$length]) : [];
    }

    /**
     * Mathematically add the given frequency to existing frequency for the entityID
     *
     * @param int $entityID
     * @param int $frequency
     * @return void
     * @internal
     */
    public function addEntityFrequency($entityID, $frequency)
    {
        if (!isset($this->frequencies[$entityID])) {
            $this->frequencies[$entityID] = 0;
        }

        $this->frequencies[$entityID] += $frequency;
    }

    /**
     * Update the entity frequencies to use actual entity names
     *
     * @param array $entityMap [entityID => entityName]
     * @return void
     */
    public function resolveEntities($entityMap) {
        $resolved = [];
        foreach ($this->frequencies as $eid => $freq) {
            $name = $entityMap[$eid];
            $resolved[$name] = $freq;
        }
        $this->frequencies = $resolved;
    }
}
