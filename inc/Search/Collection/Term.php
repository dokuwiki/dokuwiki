<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Utf8\PhpString;
use dokuwiki\Search\Tokenizer;
use dokuwiki\Utf8;

/**
 * Represents a search term that can match one or more tokens in an index
 *
 * A term can contain wildcards (* at start/end) and thus may refer to various tokens
 * of different lengths. After a CollectionSearch executes, each Term holds the full
 * match detail: which tokens matched on which entities with what frequencies.
 */
class Term
{
    public const WILDCARD_NONE = 0;
    public const WILDCARD_START = 1;
    public const WILDCARD_END = 2;

    /** @var string the original term including wildcard chars */
    protected string $original;

    /** @var string the base of the term without wildcard chars */
    protected string $base;

    /** @var string the quoted term to be used in a regular expression */
    protected string $quoted;

    /** @var int the length of the base term (not counting wildcards) */
    protected int $length;

    /** @var int The type of wildcards */
    protected int $wildcard = self::WILDCARD_NONE;

    /** @var bool Whether to match case-insensitively */
    protected bool $isCaseInsensitive = false;

    /** @var array<string, array<string, int>> Match results: [entityName => [tokenName => freq, ...], ...] */
    protected array $matches = [];

    // region Setup

    /**
     * @param string $term
     */
    public function __construct(string $term)
    {
        $this->original = $term;
        $this->base = trim($term, '*');
        $this->quoted = preg_quote_cb($this->base);
        $this->length = Tokenizer::tokenLength($this->base);

        // handle wildcard
        if (str_starts_with($term, '*')) {
            $this->quoted = '.*' . $this->quoted;
            $this->wildcard += self::WILDCARD_START;
        }

        if (str_ends_with($term, '*')) {
            $this->quoted .= '.*';
            $this->wildcard += self::WILDCARD_END;
        }
    }

    /**
     * Enable case-insensitive matching
     *
     * The fulltext token index is already lowercased by the Tokenizer, so this is only
     * needed for metadata/title searches where indexed values preserve case.
     *
     * @return static
     */
    public function caseInsensitive(): static
    {
        $this->isCaseInsensitive = true;
        $this->base = PhpString::strtolower($this->base);
        return $this;
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

    // endregion

    // region Matching

    /**
     * Check if a token value matches this term
     *
     * Uses efficient string functions instead of regex:
     * exact match → ===, wildcards → str_starts_with/str_ends_with/str_contains.
     * When caseInsensitive() is set, the token value is lowercased before comparison.
     *
     * @param string $tokenValue
     * @return bool
     */
    public function matches(string $tokenValue): bool
    {
        if ($this->isCaseInsensitive) {
            $tokenValue = PhpString::strtolower($tokenValue);
        }

        return match ($this->wildcard) {
            self::WILDCARD_NONE => $this->base === $tokenValue,
            self::WILDCARD_END => str_starts_with($tokenValue, $this->base),
            self::WILDCARD_START => str_ends_with($tokenValue, $this->base),
            default => str_contains($tokenValue, $this->base),
        };
    }

    // endregion

    // region Results (populated by CollectionSearch at the end of execute())

    /**
     * Record that a token matched an entity with a given frequency
     *
     * When called multiple times for the same entity/token pair, frequencies are summed.
     *
     * @param string $entityName
     * @param string $tokenName
     * @param int $frequency
     * @return void
     * @internal Called by CollectionSearch::resolveAndPopulateTerms()
     */
    public function addMatch(string $entityName, string $tokenName, int $frequency): void
    {
        $this->matches[$entityName][$tokenName] =
            ($this->matches[$entityName][$tokenName] ?? 0) + $frequency;
    }

    // endregion

    // region Result accessors

    /**
     * Return the full match detail
     *
     * @return array<string, array<string, int>> [entityName => [tokenName => freq, ...], ...]
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * Return the matching entities and their aggregated frequencies
     *
     * Values are the total frequency across all matching tokens for each entity.
     *
     * @return array<string, int> [entityName => totalFrequency, ...]
     */
    public function getEntityFrequencies(): array
    {
        return array_map(array_sum(...), $this->matches);
    }

    /**
     * Return the matched token names per entity
     *
     * @return array<string, string[]> [entityName => [tokenName, ...], ...]
     */
    public function getEntityTokens(): array
    {
        return array_map(array_keys(...), $this->matches);
    }

    /**
     * Return all unique matched token values
     *
     * @return string[]
     */
    public function getTokens(): array
    {
        if ($this->matches === []) return [];
        return array_keys(array_merge(...array_values($this->matches)));
    }

    // endregion
}
