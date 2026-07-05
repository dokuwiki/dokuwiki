<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\PageMetaCollection;
use dokuwiki\Search\Exception\IndexIntegrityException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Index\FileIndex;
use dokuwiki\Search\Index\Lock;
use dokuwiki\Search\Index\MemoryIndex;

class LookupCollectionTest extends \DokuWikiTest
{
    protected function tearDown(): void
    {
        Lock::releaseAll();
        parent::tearDown();
    }

    /**
     * A shared, caller-owned index lock must survive a collection lock()/unlock() cycle
     *
     * Indexer::addPage() locks the page index once, then hands it to a series of
     * collections that each lock() and unlock() around their work. The collection must
     * treat that shared, already-locked index as owned by the caller and leave its lock
     * untouched - previously the collection's unlock() released it, dropping the caller's
     * lock (and the filesystem lock) mid-operation.
     */
    public function testSharedIndexLockSurvivesCollectionCycle()
    {
        global $conf;
        // a dedicated shared index name so this mutating test cannot disturb the RIDs
        // the other tests rely on in this class's shared data dir
        $lockDir = $conf['lockdir'] . '/sharedlockentity.index';

        // caller acquires the shared entity index lock, as Indexer::addPage() does
        // with the page index
        $sharedIndex = new FileIndex('sharedlockentity', '', true);
        $this->assertTrue($sharedIndex->isWritable());
        $this->assertDirectoryExists($lockDir);

        // several collections work on the shared index in turn
        foreach (['sharedlock_one', 'sharedlock_two'] as $key) {
            (new PageMetaCollection($key, $sharedIndex))->lock()
                ->addEntity('sharedlock:start', ['wiki:a', 'wiki:b'])->unlock();

            // the caller's lock on the shared index must still be held
            $this->assertTrue($sharedIndex->isWritable(), "shared lock dropped after $key");
            $this->assertDirectoryExists($lockDir, "filesystem lock dropped after $key");
        }

        // releasing the caller's own lock finally drops it
        $sharedIndex->unlock();
        $this->assertFalse($sharedIndex->isWritable());
        $this->assertDirectoryDoesNotExist($lockDir);
    }
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
     * histogram() returns each token with the number of entities carrying it,
     * ordered by frequency and filtered by min/max/minlen
     */
    public function testHistogram()
    {
        $index = new MockLookupCollection('hist_entity', 'hist_token', 'hist_freq', 'hist_reverse');
        $index->lock();
        $index->addEntity('p:a', ['news', 'wiki', 'ab']);
        $index->addEntity('p:b', ['news']);
        $index->addEntity('p:c', ['news', 'tech']);
        $index->unlock();

        $this->assertSame(['news' => 3, 'wiki' => 1, 'tech' => 1], $index->histogram(1, 0, 3));
        $this->assertSame(['news' => 3], $index->histogram(2, 0, 3), 'min filter');
        $this->assertSame(['wiki' => 1, 'tech' => 1], $index->histogram(1, 2, 3), 'max filter');
        $this->assertArrayHasKey('ab', $index->histogram(1, 0, 2), 'minlen 2 keeps short token');

        $empty = new MockLookupCollection('histe_entity', 'histe_token', 'histe_freq', 'histe_reverse');
        $this->assertSame([], $empty->histogram());
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
     * checkIntegrity passes on a healthy non-split collection
     */
    public function testCheckIntegrityHealthy()
    {
        $index = new MockLookupCollection('cih_entity', 'cih_token', 'cih_freq', 'cih_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax']);
        $index->unlock();

        $index->checkIntegrity(); // should not throw
        $this->assertTrue(true);
    }

    /**
     * checkIntegrity passes on an empty non-split collection
     */
    public function testCheckIntegrityEmpty()
    {
        $index = new MockLookupCollection('cie_entity', 'cie_token', 'cie_freq', 'cie_reverse');
        $index->checkIntegrity(); // should not throw
        $this->assertTrue(true);
    }

    /**
     * checkIntegrity detects token/frequency mismatch on non-split collection
     */
    public function testCheckIntegrityTokenFreqMismatch()
    {
        global $conf;
        $index = new MockLookupCollection('cim_entity', 'cim_token', 'cim_freq', 'cim_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax']);
        $index->unlock();

        // corrupt: add extra line to token index
        file_put_contents($conf['indexdir'] . '/cim_token.idx', "extra\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new MockLookupCollection('cim_entity', 'cim_token', 'cim_freq', 'cim_reverse'))->checkIntegrity();
    }

    /**
     * checkIntegrity detects entity/reverse mismatch on non-split collection
     */
    public function testCheckIntegrityEntityReverseMismatch()
    {
        global $conf;
        $index = new MockLookupCollection('cir_entity', 'cir_token', 'cir_freq', 'cir_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax']);
        $index->unlock();

        // corrupt: add extra line to reverse index
        file_put_contents($conf['indexdir'] . '/cir_reverse.idx', "0\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new MockLookupCollection('cir_entity', 'cir_token', 'cir_freq', 'cir_reverse'))->checkIntegrity();
    }

    /**
     * checkIntegrity detects missing frequency index when token index exists
     */
    public function testCheckIntegrityMissingFreqIndex()
    {
        global $conf;
        $index = new MockLookupCollection('cimf_entity', 'cimf_token', 'cimf_freq', 'cimf_reverse');
        $index->lock();
        $index->addEntity('wiki:start', ['wiki:syntax']);
        $index->unlock();

        // corrupt: delete frequency index
        @unlink($conf['indexdir'] . '/cimf_freq.idx');

        $this->expectException(IndexIntegrityException::class);
        (new MockLookupCollection('cimf_entity', 'cimf_token', 'cimf_freq', 'cimf_reverse'))->checkIntegrity();
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
