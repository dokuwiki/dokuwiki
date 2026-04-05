<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\IndexAccessException;
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
        protected bool   $splitByLength = false
    )
    {
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
        foreach ([
            $this->idxEntity,
            $this->idxToken,
            $this->idxFrequency,
            $this->idxReverse
        ] as $idx) {
            if ($idx === '') continue;
            try {
                if ($idx instanceof AbstractIndex) {
                    $idx->lock();
                    $this->lockedIndexes[] = $idx;
                } else {
                    Lock::acquire($idx);
                    $this->lockedIndexes[] = $idx;
                }
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
     * @return void
     */
    public function unlock(): void
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
     * @param int|string $suffix
     * @return AbstractIndex
     * @throws IndexLockException
     */
    public function getTokenIndex(int|string $suffix): AbstractIndex
    {
        if ($this->idxToken instanceof AbstractIndex) {
            return $this->idxToken;
        }
        return new MemoryIndex($this->idxToken, $suffix, $this->isWritable);
    }

    /**
     * @param int|string $suffix
     * @return AbstractIndex
     * @throws IndexLockException
     */
    public function getFrequencyIndex(int|string $suffix): AbstractIndex
    {
        return new MemoryIndex($this->idxFrequency, $suffix, $this->isWritable);
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
     * Maximum suffix for the token indexes (eg. max word length currently stored)
     *
     * @return int
     * @throws IndexLockException
     */
    public function getTokenIndexMaximum(): int
    {
        return $this->getTokenIndex('')->max(); // no suffix needed to access the maximum
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
     * @throws IndexAccessException
     * @throws IndexWriteException
     * @throws IndexLockException
     */
    public function addEntity(string $entity, array $tokens): void
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
     * @throws IndexWriteException
     */
    protected function resolveTokens(array $tokens): array
    {
        $counted = $this->countTokens($tokens);

        // group tokens by their index suffix
        $groups = [];
        foreach ($counted as $token => $freq) {
            $group = $this->splitByLength ? (string)Tokenizer::tokenLength($token) : '';
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
        $data = array_map('array_filter', $data);
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
            $group = array_pop($parts) ?? '';
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
            $prefix = $group === '' ? '' : "$group*";
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
     * @throws IndexWriteException
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
