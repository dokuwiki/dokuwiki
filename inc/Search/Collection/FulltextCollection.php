<?php

namespace dokuwiki\Search\Collection;

use dokuwiki\Search\Exception\IndexAccessException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Exception\IndexWriteException;
use dokuwiki\Search\Index\AbstractIndex;
use dokuwiki\Search\Index\FileIndex;
use dokuwiki\Search\Index\Lock;
use dokuwiki\Search\Index\MemoryIndex;
use dokuwiki\Search\Index\TupleOps;
use dokuwiki\Search\Tokenizer;

/**
 * Manage a fulltext index collection
 *
 * This is a typical search index, where the primary identity is something like a page containing text that should be
 * searchable by the words on the page
 *
 * @todo check if Index Accessor classes are correct (File vs. Memory)
 * @todo decide which parts to move into an abstract base class
 * @todo implement similar class for non-frequency based indexes
 * @todo implement specific WikiPageCollection that predefines the index names
 * @todo maybe rename into FrequencyCollection
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class FulltextCollection
{

    /** @var string Index name of the primary entity */
    protected $idxEntity;
    /** @var string Index base name of the secondary entity */
    protected $idxToken;
    /** @var string Index base name of the frequencies */
    protected $idxFrequency;
    /** @var string Index name of the reverse index */
    protected $idxReverse;

    /** @var bool has a lock been acquired for all used indexes? */
    protected $isWritable = false;

    /**
     * A fulltext collection
     *
     * This accesses an index collection that stores the frequency of tokens assigned to an entity. A reverse index
     * is used to keep track what tokens are  assigned to each entity
     *
     * Example: the frequency of words on a page.
     *
     * @param string $idxEntity Name of the primary entity index, eg. 'page'
     * @param string $idxToken Base name of the secondary entity index, eg. 'w' for words
     * @param string $idxFrequency Base name of the frequency index, eg. 'i' for word frequencies
     * @param string $idxReverse Name of the of the reverse index, eg, 'pageword' to search by page instead of by word
     */
    public function __construct($idxEntity, $idxToken, $idxFrequency, $idxReverse)
    {
        $this->idxEntity = $idxEntity;
        $this->idxToken = $idxToken;
        $this->idxFrequency = $idxFrequency;
        $this->idxReverse = $idxReverse;
    }

    /**
     * Destructor
     *
     * Ensures locks are released when the class is destroyed
     */
    public function __destruct()
    {
        if ($this->isWritable) {
            $this->unlock();
        }
    }

    /**
     * Lock all indexes for writing
     *
     * @return $this can be used for chaining
     * @throws IndexLockException
     */
    public function lock()
    {
        foreach ([$this->idxEntity, $this->idxToken, $this->idxFrequency, $this->idxReverse] as $idxName) {
            if (!(new Lock($idxName))->acquire()) {
                $this->unlock(); // release any already acquired locks
                throw new IndexLockException('Could not lock ' . $idxName . ' for writing');
            }
        }
        // locking succeeded
        $this->isWritable = true;
        return $this;
    }

    /**
     * Unlock all indexes
     *
     * @return void
     */
    public function unlock()
    {
        foreach ([$this->idxEntity, $this->idxToken, $this->idxFrequency, $this->idxReverse] as $idxName) {
            (new Lock($idxName))->release();
        }
        $this->isWritable = false;
    }

    /**
     * @return FileIndex
     */
    public function getEntityIndex()
    {
        return new FileIndex($this->idxEntity, '', $this->isWritable);
    }

    /**
     * @param int|string $suffix
     * @return MemoryIndex
     */
    public function getTokenIndex($suffix)
    {
        return new MemoryIndex($this->idxToken, $suffix, $this->isWritable);
    }

    /**
     * @param int|string $suffix
     * @return MemoryIndex
     */
    public function getFrequencyIndex($suffix)
    {
        return new MemoryIndex($this->idxFrequency, $suffix, $this->isWritable);
    }

    /**
     * @return FileIndex
     */
    public function getReverseIndex()
    {
        return new FileIndex($this->idxReverse, '', $this->isWritable);
    }

    /**
     * Add or update the tokens for a given entity
     *
     * The given list of tokens replaces the previusly stored list for that entity. An empty list removes the
     * entity from the index
     *
     * @param string $entity the name of the entity
     * @param string[] $tokens the list of tokens for this entity
     *
     * @throws IndexAccessException
     * @throws IndexWriteException
     * @throws IndexLockException
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function addEntity($entity, $tokens)
    {
        if(!$this->isWritable) {
            throw new IndexLockException('Indexes not locked. Forgot to call lock()?');
        }

        $entityIndex = $this->getEntityIndex();
        $entityId = $entityIndex->accessCachedValue($entity);

        $old = $this->getReverseAssignments($entity); // assumes a frequency of 0
        $new = $this->getTokenFrequency($tokens); // the real frequencies

        $frequencies = array_replace_recursive(
            $old,
            $new
        );

        // store word frequency
        foreach (array_keys($frequencies) as $tokenLength) {
            $freqIndex = $this->getFrequencyIndex($tokenLength);
            foreach ($frequencies[$tokenLength] as $tokenId => $freq) {
                $record = $freqIndex->retrieveRow($tokenId);
                $record = TupleOps::updateTuple($record, $entityId, $freq); // frequency of 0 deletes
                $freqIndex->changeRow($tokenId, $record);

                if (isset($oldwords[$tokenLength][$tokenId])) {
                    unset($oldwords[$tokenLength][$tokenId]);
                }
            }
            $freqIndex->save();
        }

        // update reverse Index
        $this->saveReverseAssignments($entity, $frequencies);
    }

    /**
     * Maximum suffix for the token indexes (eg. max word length currently stored)
     * @return int
     */
    public function getTokenIndexMaximum()
    {
        return $this->getTokenIndex('')->max(); // no suffix needed to access the maximum
    }


    /**
     *
     * TokenIDs assigned to the given Entity sorted by token length as stored in the reverse Index
     *
     * Returns an Array in the form [tokenLength => [TokenId => 0, ...], ...]. The fixed 0 ensures array structure
     * compatibility with getTokenFrequency() and is used to remove no longer used tokens.
     *
     * @param string $entity
     * @return array
     * @throws IndexAccessException
     * @throws IndexWriteException
     */
    public function getReverseAssignments($entity)
    {
        $pageIndex = $this->getEntityIndex();
        $entityId = $pageIndex->accessCachedValue($entity);

        $pageRevIndex = $this->getReverseIndex();
        $record = $pageRevIndex->retrieveRow($entityId);

        $result = [];
        if ($record === '') {
            return $result;
        }

        foreach (explode(':', $record) as $row) {
            list($tokenLength, $tokenId) = explode('*', $row);
            $result[$tokenLength][$tokenId] = 0;
        }

        return $result;
    }

    /**
     * Store the reverse index info about what tokens are assigned to the entity
     *
     * @param string $entity
     * @param array $frequencies
     * @return void
     * @throws IndexAccessException
     * @throws IndexWriteException
     */
    protected function saveReverseAssignments($entity, $frequencies)
    {
        $frequencies = array_filter($frequencies); // remove all non-used words

        $record = '';
        foreach (array_keys($frequencies) as $tokenLength) {
            foreach (array_keys($frequencies[$tokenLength]) as $tokenId) {
                $record .= "$tokenLength*$tokenId:";
            }
        }
        $record = trim($record, ':');

        $pageIndex = $this->getEntityIndex();
        $entityId = $pageIndex->accessCachedValue($entity);

        $pageRevIndex = $this->getReverseIndex();
        $pageRevIndex->changeRow($entityId, $record);
    }

    /**
     * Count the given tokens, add them to index and return a frequency table
     *
     * Returns TokenIDs and their frequency sorted by token length
     *
     * @param string[] $tokens
     * @return array frequency table
     *
     * @throws IndexWriteException
     * @throws IndexLockException
     * @author Christopher Smith <chris@jalakai.co.uk>
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function getTokenFrequency($tokens)
    {
        $tokens = array_count_values($tokens);  // count the frequency of each token

        // sub-sort by word length: $words = [wordlen => [word => frequency]]
        $tokenList = [];
        foreach ($tokens as $token => $count) {
            $tokenLength = Tokenizer::tokenLength($token);
            if (isset($tokenList[$tokenLength])) {
                $tokenList[$tokenLength][$token] = $count + ($tokenList[$tokenLength][$token] ?? 0);
            } else {
                $tokenList[$tokenLength] = [$token => $count];
            }
        }

        // convert words into wordIDs (new words are saved back to the appropriate index files)
        $result = [];
        foreach (array_keys($tokenList) as $tokenLength) {
            $result[$tokenLength] = [];
            $wordIndex = $this->getTokenIndex($tokenLength);
            foreach ($tokenList[$tokenLength] as $token => $freq) {
                $tokenId = $wordIndex->getRowID((string)$token);
                $result[$tokenLength][$tokenId] = $freq;
            }
            $wordIndex->save();
        }

        return $result;
    }
}
