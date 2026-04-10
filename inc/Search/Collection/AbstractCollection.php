<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\IndexAccessException;
use dokuwiki\Search\Exception\IndexIntegrityException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Exception\IndexUsageException;
use dokuwiki\Search\Exception\IndexWriteException;
use dokuwiki\Search\Index\AbstractIndex;
use dokuwiki\Search\Index\FileIndex;
use dokuwiki\Search\Index\Lock;
use dokuwiki\Search\Index\MemoryIndex;
use dokuwiki\Search\Index\TupleOps;
use dokuwiki\Search\Tokenizer;

/**
 * Abstract base class for index collections
 *
 * A collection manages a group of related indexes that together provide a specific search use case.
 * Every collection works with four index types: entity, token, frequency, and reverse.
 *
 * entity - the list of the main entities (eg. pages)
 * token - the list of tokens (eg. words) assigned to entities (can be split into multiple files)
 * frequency - how often a token appears on a entity (can be split into multiple files)
 * reverse - the list of tokens assigned to each entity
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
abstract class AbstractCollection
{
    /** @var array<string|AbstractIndex> Index names or objects that have been successfully locked */
    protected array $lockedIndexes = [];

    /** @var bool Has a lock been acquired for all used indexes? */
    protected bool $isWritable = false;

    /**
     * Initialize the collection with the names of the indexes it manages
     *
     * Entity and token indexes can be passed as already instantiated AbstractIndex objects
     * for sharing between collections. When $idxToken is an object, $splitByLength must be false.
     *
     * @param string|AbstractIndex $idxEntity Name or instance of the primary entity index, eg. 'page'
     * @param string|AbstractIndex $idxToken Name or instance of the secondary entity index, eg. 'w' for words
     * @param string $idxFrequency Base name of the frequency index, eg. 'i' for word frequencies
     * @param string $idxReverse Name of the reverse index, eg. 'pageword'
     * @param bool $splitByLength Whether to split token/frequency indexes by token length
     * @throws IndexUsageException
     */
    public function __construct(
        protected string|AbstractIndex $idxEntity,
        protected string|AbstractIndex $idxToken,
        protected string $idxFrequency = '',
        protected string $idxReverse = '',
        protected bool $splitByLength = false
    ) {
        if ($idxToken instanceof AbstractIndex && $splitByLength) {
            throw new IndexUsageException('Cannot split by length when using a pre-instantiated token index');
        }
    }

    /**
     * Destructor
     *
     * Ensures locks are released when the class is destroyed
     */
    public function __destruct()
    {
        $this->unlock();
    }

    /**
     * Lock all indexes for writing
     *
     * @return $this can be used for chaining
     * @throws IndexLockException
     */
    public function lock(): static
    {
        foreach (
            [
            $this->idxEntity,
            $this->idxToken,
            $this->idxFrequency,
            $this->idxReverse
            ] as $idx
        ) {
            if ($idx === '') continue;
            try {
                if ($idx instanceof AbstractIndex) {
                    $idx->lock();
                } else {
                    Lock::acquire($idx);
                }
                $this->lockedIndexes[] = $idx;
            } catch (IndexLockException $e) {
                $this->unlock();
                throw $e;
            }
        }
        $this->isWritable = true;
        return $this;
    }

    /**
     * Unlock all indexes that were successfully locked
     *
     * @return static
     */
    public function unlock(): static
    {
        foreach ($this->lockedIndexes as $idx) {
            if ($idx instanceof AbstractIndex) {
                $idx->unlock();
            } else {
                Lock::release($idx);
            }
        }
        $this->lockedIndexes = [];
        $this->isWritable = false;
        return $this;
    }

    /**
     * @return AbstractIndex
     * @throws IndexLockException
     */
    public function getEntityIndex(): AbstractIndex
    {
        if ($this->idxEntity instanceof AbstractIndex) {
            return $this->idxEntity;
        }
        return new FileIndex($this->idxEntity, '', $this->isWritable);
    }

    /**
     * @param int $group Index group (0 for non-split, token length for split)
     * @return AbstractIndex
     * @throws IndexLockException
     */
    public function getTokenIndex(int $group = 0): AbstractIndex
    {
        if ($this->idxToken instanceof AbstractIndex) {
            return $this->idxToken;
        }
        return new MemoryIndex($this->idxToken, $this->groupToSuffix($group), $this->isWritable);
    }

    /**
     * @param int $group Index group (0 for non-split, token length for split)
     * @return AbstractIndex
     * @throws IndexLockException
     */
    public function getFrequencyIndex(int $group = 0): AbstractIndex
    {
        return new MemoryIndex($this->idxFrequency, $this->groupToSuffix($group), $this->isWritable);
    }

    /**
     * @return AbstractIndex
     * @throws IndexLockException
     */
    public function getReverseIndex(): AbstractIndex
    {
        return new FileIndex($this->idxReverse, '', $this->isWritable);
    }

    /**
     * Whether this collection splits token/frequency indexes by token length
     *
     * @return bool
     */
    public function isSplitByLength(): bool
    {
        return $this->splitByLength;
    }

    /**
     * Convert a logical group number to the index file suffix
     *
     * Group 0 represents non-split indexes (suffix '') while positive integers
     * represent split-by-length indexes (suffix = the length).
     *
     * @param int $group
     * @return string The file suffix ('' for group 0, the group number as string otherwise)
     * @throws IndexUsageException when group does not match the collection's split mode
     */
    protected function groupToSuffix(int $group): string
    {
        if ($group === 0 && $this->splitByLength) {
            throw new IndexUsageException('Group 0 is not valid for split-by-length collections');
        }
        if ($group !== 0 && !$this->splitByLength) {
            throw new IndexUsageException("Group $group is not valid for non-split collections");
        }
        return $group === 0 ? '' : (string)$group;
    }

    /**
     * Resolve token IDs to entity frequencies
     *
     * Given a set of token IDs from a specific index group, returns the entities
     * that have those tokens and their frequencies. This encapsulates the frequency
     * index access so that subclasses (e.g. DirectCollection) can provide alternative
     * mappings.
     *
     * @param int $group Index group (0 for non-split, token length for split)
     * @param int[] $tokenIds The token IDs to resolve
     * @return array [tokenId => [entityId => frequency, ...], ...]
     */
    public function resolveTokenFrequencies(int $group, array $tokenIds): array
    {
        $freqIndex = $this->getFrequencyIndex($group);
        if (!$freqIndex->exists()) return [];
        return array_map(TupleOps::parseTuples(...), $freqIndex->retrieveRows($tokenIds));
    }

    /**
     * Return all entity names that have data in this collection
     *
     * @return string[] entity names
     */
    public function getEntitiesWithData(): array
    {
        $entityIndex = $this->getEntityIndex();

        // collect entity IDs from all frequency index groups
        $max = $this->splitByLength ? $this->getTokenIndexMaximum() : 0;
        $groups = $this->splitByLength ? ($max > 0 ? range(1, $max) : []) : [0];

        $entityIds = [];
        foreach ($groups as $group) {
            $freqIndex = $this->getFrequencyIndex($group);
            if (!$freqIndex->exists()) continue;
            foreach ($freqIndex as $line) {
                foreach (array_keys(TupleOps::parseTuples($line)) as $entityId) {
                    $entityIds[$entityId] = true;
                }
            }
        }

        $names = $entityIndex->retrieveRows(array_keys($entityIds));
        return array_values(array_filter($names, static fn($v) => $v !== ''));
    }

    /**
     * Maximum suffix for the token indexes (eg. max word length currently stored)
     *
     * @return int
     * @throws IndexLockException
     */
    public function getTokenIndexMaximum(): int
    {
        if ($this->idxToken instanceof AbstractIndex) {
            return $this->idxToken->max();
        }
        return (new MemoryIndex($this->idxToken, ''))->max();
    }

    /**
     * Check the structural integrity of this collection's indexes
     *
     * Verifies that paired indexes have matching line counts:
     * - token == frequency (per group, both keyed by token RID)
     * - entity == reverse (both keyed by entity RID)
     *
     * @throws IndexIntegrityException when a structural inconsistency is found
     */
    public function checkIntegrity(): void
    {
        // Check token/frequency pairs
        $max = $this->splitByLength ? $this->getTokenIndexMaximum() : 0;
        $groups = $this->splitByLength ? ($max > 0 ? range(1, $max) : []) : [0];

        foreach ($groups as $group) {
            $tokenIndex = $this->getTokenIndex($group);
            $freqIndex = $this->getFrequencyIndex($group);

            if (!$tokenIndex->exists() && !$freqIndex->exists()) continue;

            if ($tokenIndex->exists() !== $freqIndex->exists()) {
                throw new IndexIntegrityException(
                    "Group $group: missing " .
                    ($tokenIndex->exists() ? 'frequency' : 'token') . ' index'
                );
            }

            $tc = count($tokenIndex);
            $fc = count($freqIndex);
            if ($tc !== $fc) {
                throw new IndexIntegrityException(
                    "Group $group: token count ($tc) != frequency count ($fc)"
                );
            }
        }

        // Check entity/reverse pair
        $entityIndex = $this->getEntityIndex();
        $reverseIndex = $this->getReverseIndex();
        if ($entityIndex->exists() && $reverseIndex->exists()) {
            $ec = count($entityIndex);
            $rc = count($reverseIndex);
            if ($ec !== $rc) {
                throw new IndexIntegrityException(
                    "Entity count ($ec) != reverse count ($rc)"
                );
            }
        }
    }

    /**
     * Add or update the tokens for a given entity
     *
     * The given list of tokens replaces the previously stored list for that entity. An empty list removes the
     * entity from the index.
     *
     * The update merges old and new token data. getReverseAssignments() returns all previously stored token IDs
     * with a value of 0 (see parseReverseRecord). resolveTokens() returns the new token IDs with their values.
     * After array_replace_recursive, tokens only in the old map keep value 0 — causing updateIndexes to delete
     * them from the frequency index via TupleOps::updateTuple. Tokens in the new map overwrite with their value.
     *
     * @param string $entity The name of the entity
     * @param string[] $tokens The list of tokens for this entity
     * @return static
     * @throws IndexAccessException
     * @throws IndexWriteException
     * @throws IndexLockException
     */
    public function addEntity(string $entity, array $tokens): static
    {
        if (!$this->isWritable) {
            throw new IndexLockException('Indexes not locked. Forgot to call lock()?');
        }

        $entityIndex = $this->getEntityIndex();
        $entityId = $entityIndex->accessCachedValue($entity);

        $old = $this->getReverseAssignments($entity);
        $new = $this->resolveTokens($tokens);

        $merged = array_replace_recursive($old, $new);

        $this->updateIndexes($merged, $entityId);
        $this->saveReverseAssignments($entity, $merged);

        return $this;
    }

    /**
     * Resolve raw tokens into the two-level structure [group => [tokenId => frequency]]
     *
     * Calls countTokens() to get token frequencies (subclass responsibility), then groups
     * by token length if splitByLength is enabled, or under '' if not. Finally resolves
     * token strings to IDs via the appropriate token index.
     *
     * @param string[] $tokens The raw token list
     * @return array [group => [tokenId => frequency, ...], ...]
     * @throws IndexLockException
     */
    protected function resolveTokens(array $tokens): array
    {
        $counted = $this->countTokens($tokens);

        // group tokens by their index suffix
        $groups = [];
        foreach ($counted as $token => $freq) {
            $group = $this->splitByLength ? Tokenizer::tokenLength($token) : 0;
            $groups[$group][$token] = $freq;
        }

        // resolve token strings to IDs
        $result = [];
        foreach ($groups as $group => $tokenFreqs) {
            $tokenIndex = $this->getTokenIndex($group);
            $result[$group] = [];
            foreach ($tokenFreqs as $token => $freq) {
                $tokenId = $tokenIndex->getRowID((string)$token);
                $result[$group][$tokenId] = $freq;
            }
            $tokenIndex->save();
        }

        return $result;
    }

    /**
     * Count or deduplicate tokens and return their frequencies
     *
     * FrequencyCollections return actual occurrence counts.
     * LookupCollections deduplicate and return 1 for each token.
     *
     * @param string[] $tokens The raw token list
     * @return array [token => frequency, ...]
     */
    abstract protected function countTokens(array $tokens): array;

    /**
     * Get the token assignments for a given entity from the reverse index
     *
     * Returns the parsed reverse index record. The exact structure depends on the collection type.
     *
     * @param string $entity
     * @return array
     * @throws IndexAccessException
     * @throws IndexWriteException
     * @throws IndexLockException
     */
    public function getReverseAssignments(string $entity): array
    {
        $entityIndex = $this->getEntityIndex();
        $entityId = $entityIndex->accessCachedValue($entity);

        $reverseIndex = $this->getReverseIndex();
        $record = $reverseIndex->retrieveRow($entityId);

        if ($record === '') {
            return [];
        }

        return $this->parseReverseRecord($record);
    }

    /**
     * Store the reverse index info about what tokens are assigned to the entity
     *
     * @param string $entity
     * @param array $data The assignment data to store
     * @return void
     * @throws IndexAccessException
     * @throws IndexWriteException
     * @throws IndexLockException
     */
    protected function saveReverseAssignments(string $entity, array $data): void
    {
        // remove tokens with frequency 0 (no longer assigned), then remove empty groups
        $data = array_map(array_filter(...), $data);
        $data = array_filter($data);

        $record = $this->formatReverseRecord($data);

        $entityIndex = $this->getEntityIndex();
        $entityId = $entityIndex->accessCachedValue($entity);

        $reverseIndex = $this->getReverseIndex();
        $reverseIndex->changeRow($entityId, $record);
    }

    /**
     * Parse a reverse index record into a two-level array
     *
     * The reverse index only stores which token IDs belong to an entity, not their frequencies. All values
     * in the returned array are set to 0. This is intentional: when merged with new data in addEntity(),
     * tokens absent from the new data retain 0, signaling deletion from the frequency index.
     *
     * For split collections the format is "group*tokenId:group*tokenId:..." where group is the token length.
     * For non-split collections the group prefix is omitted: "tokenId:tokenId:..."
     * This mirrors how TupleOps omits *1 for frequency 1.
     *
     * @param string $record The raw reverse index record
     * @return array [group => [tokenId => 0, ...], ...]
     */
    protected function parseReverseRecord(string $record): array
    {
        $result = [];
        foreach (explode(':', $record) as $entry) {
            $parts = explode('*', $entry, 2);
            $tokenId = array_pop($parts);
            $group = (int)(array_pop($parts) ?? 0);
            $result[$group][$tokenId] = 0;
        }
        return $result;
    }

    /**
     * Format a two-level array into a reverse index record string
     *
     * @param array $data [group => [tokenId => freq, ...], ...]
     * @return string The formatted record
     */
    protected function formatReverseRecord(array $data): string
    {
        $parts = [];
        foreach ($data as $group => $tokens) {
            $prefix = $group === 0 ? '' : "$group*";
            foreach (array_keys($tokens) as $tokenId) {
                $parts[] = $prefix . $tokenId;
            }
        }
        return implode(':', $parts);
    }

    /**
     * Update frequency indexes with the given data
     *
     * Iterates over the two-level structure [group => [tokenId => freq]] and updates the
     * corresponding frequency index for each group. A frequency of 0 removes the entity
     * from that token's frequency record.
     *
     * @param array $data [group => [tokenId => frequency, ...], ...]
     * @param int $entityId The entity ID
     * @throws IndexLockException
     */
    protected function updateIndexes(array $data, int $entityId): void
    {
        foreach ($data as $group => $tokens) {
            $freqIndex = $this->getFrequencyIndex($group);
            foreach ($tokens as $tokenId => $freq) {
                $record = $freqIndex->retrieveRow($tokenId);
                $record = TupleOps::updateTuple($record, $entityId, $freq);
                $freqIndex->changeRow($tokenId, $record);
            }
            $freqIndex->save();
        }
    }
}
