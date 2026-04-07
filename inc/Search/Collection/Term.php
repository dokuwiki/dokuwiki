<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Tokenizer;

/**
 * Represents a term that is searched on a frequency based index
 *
 * A term can contain wildcards and thus may refer to various tokens of different lengths.
 */
class Term
{

    const WILDCARD_NONE = 0;
    const WILDCARD_START = 1;
    const WILDCARD_END = 2;

    /** @var string the original term including wildcard chars */
    protected string $original;

    /** @var string the base of the term without wildcard chars */
    protected string $base;

    /** @var string the quoted term to be used in a regular expression */
    protected string $quoted;

    /** @var int the length of the base term (not counting wildcards) */
    protected int $length;

    /** @var int The type of wildcards */
    protected int $wildcard;

    /** @var array<int, array<int, string>> The matching tokens for this term, keyed by group then token ID */
    protected array $tokens = [];

    /** @var array<int|string, int> The entity frequencies this term matches (aggregated over all tokens), keyed by entity ID or name */
    protected array $frequencies = [];

    /**
     * @param string $term
     * @throws SearchException
     */
    public function __construct(string $term)
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
    public function getOriginal(): string
    {
        return $this->original;
    }

    /**
     * @return string
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * @return string
     */
    public function getQuoted(): string
    {
        return $this->quoted;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getWildcard(): int
    {
        return $this->wildcard;
    }

    /**
     * @return array [entity => frequency, ...]
     */
    public function getEntityFrequencies(): array
    {
        return $this->frequencies;
    }

    /**
     * Add found token IDs for a specific index group
     *
     * @param int $group Index group (length for split collections, 0 for non-split)
     * @param array $tokens [tokenID => tokenName, ...]
     * @return void
     * @internal
     */
    public function addTokens(int $group, array $tokens): void
    {
        $this->tokens[$group] = [];
        foreach ($tokens as $tokenID => $tokenName) {
            $this->tokens[$group][$tokenID] = $tokenName;
        }
    }

    /**
     * Return all tokens that match the given term
     *
     * @return string[]
     */
    public function getTokens(): array
    {
        if (empty($this->tokens)) return [];
        return array_merge(...array_map('array_values', array_values($this->tokens)));
    }

    /**
     * Return all token IDs for a specific index group
     *
     * @param int $group Index group (length for split collections, 0 for non-split)
     * @return int[]
     */
    public function getTokenIDsByGroup(int $group): array
    {
        return isset($this->tokens[$group]) ? array_keys($this->tokens[$group]) : [];
    }

    /**
     * Mathematically add the given frequency to existing frequency for the entityID
     *
     * @param int $entityID
     * @param int $frequency
     * @return void
     * @internal
     */
    public function addEntityFrequency(int $entityID, int $frequency): void
    {
        if (!isset($this->frequencies[$entityID])) {
            $this->frequencies[$entityID] = 0;
        }

        $this->frequencies[$entityID] += $frequency;
    }

    /**
     * Update the entity frequencies to use actual entity names
     *
     * @param array<int, string> $entityMap [entityID => entityName]
     * @return void
     */
    public function resolveEntities(array $entityMap): void
    {
        $resolved = [];
        foreach ($this->frequencies as $eid => $freq) {
            $name = $entityMap[$eid];
            $resolved[$name] = $freq;
        }
        $this->frequencies = $resolved;
    }
}
