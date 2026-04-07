<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\PageMetaCollection;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Index\MemoryIndex;

class LookupCollectionTest extends \DokuWikiTest
{
    /**
     * Add data and directly check the underlying indexes for correctness
     */
    public function testAddEntity()
    {
        $index = new MockLookupCollection('a_entity', 'a_token', 'a_freq', 'a_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:logo.png', 'wiki:banner.jpg', 'wiki:icon.svg']);
        $index->unlock();

        // check entity index
        $idxEntity = new MemoryIndex('a_entity');
        $this->assertEquals('wiki:start', $idxEntity->retrieveRow(0));

        // check token index (single file, no suffix)
        $idxToken = new MemoryIndex('a_token');
        $this->assertEquals('wiki:logo.png', $idxToken->retrieveRow(0));
        $this->assertEquals('wiki:banner.jpg', $idxToken->retrieveRow(1));
        $this->assertEquals('wiki:icon.svg', $idxToken->retrieveRow(2));

        // check frequency index — all frequencies are 1 (written without *1)
        $idxFreq = new MemoryIndex('a_freq');
        $this->assertEquals('0', $idxFreq->retrieveRow(0)); // entity 0 with implicit freq 1
        $this->assertEquals('0', $idxFreq->retrieveRow(1));
        $this->assertEquals('0', $idxFreq->retrieveRow(2));

        // check reverse index
        $idxRev = new MemoryIndex('a_reverse');
        $this->assertEquals('0:1:2', $idxRev->retrieveRow(0));
    }

    /**
     * Duplicate tokens should be deduplicated
     */
    public function testAddEntityDedup()
    {
        $index = new MockLookupCollection('b_entity', 'b_token', 'b_freq', 'b_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:logo.png', 'wiki:logo.png', 'wiki:banner.jpg']);
        $index->unlock();

        $idxToken = new MemoryIndex('b_token');
        $this->assertEquals('wiki:logo.png', $idxToken->retrieveRow(0));
        $this->assertEquals('wiki:banner.jpg', $idxToken->retrieveRow(1));

        $idxRev = new MemoryIndex('b_reverse');
        $this->assertEquals('0:1', $idxRev->retrieveRow(0));
    }

    /**
     * Updating an entity should remove old tokens and add new ones
     */
    public function testUpdateEntity()
    {
        $index = new MockLookupCollection('c_entity', 'c_token', 'c_freq', 'c_reverse');

        // initial add
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:logo.png', 'wiki:banner.jpg']);
        $index->unlock();

        // update: remove logo, keep banner, add icon
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:banner.jpg', 'wiki:icon.svg']);
        $index->unlock();

        // logo should be removed from frequency index
        $idxFreq = new MemoryIndex('c_freq');
        $this->assertEquals('', $idxFreq->retrieveRow(0)); // logo removed
        $this->assertEquals('0', $idxFreq->retrieveRow(1)); // banner still on entity 0
        $this->assertEquals('0', $idxFreq->retrieveRow(2)); // icon added on entity 0

        // reverse index should only have banner and icon
        $idxRev = new MemoryIndex('c_reverse');
        $this->assertEquals('1:2', $idxRev->retrieveRow(0));
    }

    /**
     * Test reverse assignments returns two-level structure with empty group key
     */
    public function testReverseAssignments()
    {
        $index = new MockLookupCollection('d_entity', 'd_token', 'd_freq', 'd_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:logo.png', 'wiki:banner.jpg']);
        $index->unlock();

        $result = $index->getReverseAssignments('wiki:start');
        $this->assertEquals([0 => [0 => 0, 1 => 0]], $result);
    }

    /**
     * Adding entity without lock should throw exception
     */
    public function testAddEntityWithoutLock()
    {
        $this->expectException(IndexLockException::class);

        $index = new MockLookupCollection();
        $index->addEntity('wiki:start', ['wiki:logo.png']);
    }

    /**
     * Adding empty token list should clear entity from indexes
     */
    public function testEmptyTokens()
    {
        $index = new MockLookupCollection('f_entity', 'f_token', 'f_freq', 'f_reverse');

        // add some tokens first
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:logo.png']);
        $index->unlock();

        // now clear
        $index->lock();
        $index->addEntity('wiki:start', []);
        $index->unlock();

        // frequency index should be empty for this token
        $idxFreq = new MemoryIndex('f_freq');
        $this->assertEquals('', $idxFreq->retrieveRow(0));

        // reverse index should be empty
        $idxRev = new MemoryIndex('f_reverse');
        $this->assertEquals('', $idxRev->retrieveRow(0));
    }

    /**
     * Test that PageMetaCollection('relation_media') uses correct index names
     */
    public function testMediaCollection()
    {
        $index = new PageMetaCollection('relation_media');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:logo.png', 'wiki:banner.jpg']);
        $index->unlock();

        $idxToken = new MemoryIndex('relation_media_w');
        $this->assertEquals('wiki:logo.png', $idxToken->retrieveRow(0));
        $this->assertEquals('wiki:banner.jpg', $idxToken->retrieveRow(1));

        $idxRev = new MemoryIndex('relation_media_p');
        $this->assertEquals('0:1', $idxRev->retrieveRow(0));
    }

    /**
     * Test that PageMetaCollection('relation_references') uses correct index names
     */
    public function testReferencesCollection()
    {
        $index = new PageMetaCollection('relation_references');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $index->unlock();

        $idxToken = new MemoryIndex('relation_references_w');
        $this->assertEquals('wiki:syntax', $idxToken->retrieveRow(0));
        $this->assertEquals('wiki:welcome', $idxToken->retrieveRow(1));

        $idxRev = new MemoryIndex('relation_references_p');
        $this->assertEquals('0:1', $idxRev->retrieveRow(0));

        $result = $index->getReverseAssignments('wiki:start');
        $this->assertEquals([0 => [0 => 0, 1 => 0]], $result);
    }

    /**
     * resolveTokens should deduplicate and assign frequency 1 under group 0
     */
    public function testResolveTokens()
    {
        $index = new MockLookupCollection('rt_entity', 'rt_token', 'rt_freq', 'rt_reverse');
        $index->lock();

        $result = $this->callInaccessibleMethod($index, 'resolveTokens', [
            ['wiki:logo.png', 'wiki:banner.jpg', 'wiki:logo.png'],
        ]);

        // all tokens under group 0 (non-split)
        $this->assertArrayHasKey(0, $result);
        $this->assertCount(2, $result[0]); // deduplicated

        // token IDs are sequential: logo=0, banner=1
        $this->assertEquals(1, $result[0][0]); // logo freq=1
        $this->assertEquals(1, $result[0][1]); // banner freq=1
    }

    /**
     * resolveTokens with empty input should return empty array
     */
    public function testResolveTokensEmpty()
    {
        $index = new MockLookupCollection('rte_entity', 'rte_token', 'rte_freq', 'rte_reverse');
        $index->lock();

        $result = $this->callInaccessibleMethod($index, 'resolveTokens', [[]]);

        $this->assertEmpty($result);
    }

    /**
     * countTokens should deduplicate and assign frequency 1
     */
    public function testCountTokens()
    {
        $index = new MockLookupCollection();

        $result = $this->callInaccessibleMethod($index, 'countTokens', [
            ['wiki:logo.png', 'wiki:banner.jpg', 'wiki:logo.png'],
        ]);

        $this->assertEquals([
            'wiki:logo.png' => 1,
            'wiki:banner.jpg' => 1,
        ], $result);
    }

    /**
     * getEntitiesWithData returns entities that have frequency data
     */
    public function testGetEntitiesWithData()
    {
        $index = new MockLookupCollection('ewd_entity', 'ewd_token', 'ewd_freq', 'ewd_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $index->addEntity('wiki:other', ['wiki:syntax']);
        $index->addEntity('wiki:empty', []);
        $index->unlock();

        $result = $index->getEntitiesWithData();
        sort($result);
        $this->assertEquals(['wiki:other', 'wiki:start'], $result);
    }

    /**
     * resolveTokenFrequencies returns entity frequencies for given token IDs
     */
    public function testResolveTokenFrequencies()
    {
        $index = new MockLookupCollection('rtf_entity', 'rtf_token', 'rtf_freq', 'rtf_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $index->addEntity('wiki:other', ['wiki:syntax']);
        $index->unlock();

        // token ID 0 = wiki:syntax, referenced by both entities
        $result = $index->resolveTokenFrequencies(0, [0]);
        $this->assertArrayHasKey(0, $result);
        $this->assertCount(2, $result[0]); // two entities have this token
    }

    /**
     * groupToSuffix throws on non-0 group for non-split collection
     */
    public function testGroupToSuffixValidation()
    {
        $this->expectException(\dokuwiki\Search\Exception\IndexUsageException::class);

        $index = new MockLookupCollection('gs_entity', 'gs_token', 'gs_freq', 'gs_reverse');
        // non-split collection should reject group 5
        $index->getTokenIndex(5);
    }
}
