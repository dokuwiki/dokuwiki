<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Exception\IndexIntegrityException;
use dokuwiki\Search\Index\MemoryIndex;

class FrequencyCollectionTest extends \DokuWikiTest
{

    /**
     * Add data and directly check the underlying indexes for correctness
     */
    public function testDirectly()
    {
        $index = new MockFrequencyCollection('entity', 'token', 'freq', 'reverse');

        $tokens = ['one', 'two', 'three', 'four', 'two'];
        $index->lock();
        $index->addEntity('test', $tokens);
        $index->unlock();

        $idxEntity = new MemoryIndex('entity');
        $this->assertEquals('test', $idxEntity->retrieveRow(0));

        $idxToken = new MemoryIndex('token', '3');
        $this->assertEquals('one', $idxToken->retrieveRow(0));
        $this->assertEquals('two', $idxToken->retrieveRow(1));

        $idxFreq = new MemoryIndex('freq', '3');
        $this->assertEquals('0', $idxFreq->retrieveRow(0)); // one is 1x on page 0 (written without *1)
        $this->assertEquals('0*2', $idxFreq->retrieveRow(1)); // two is 2x on page 0

        $idxRev = new MemoryIndex('reverse');
        $this->assertEquals('3*0:3*1:5*0:4*0', $idxRev->retrieveRow(0));

        // remove one of the tokens
        $tokens = ['two', 'three', 'four', 'two'];
        $index->lock();
        $index->addEntity('test', $tokens);
        $index->unlock();

        $idxFreq = new MemoryIndex('freq', '3');
        $this->assertEquals('', $idxFreq->retrieveRow(0)); // one is not on page 0
    }

    /**
     * Test reverse lookup
     *
     * A lookup for the page should return the word frequencies
     */
    public function testReverse()
    {
        $index = new MockFrequencyCollection('page', 'word', 'w', 'pageword');
        $index->lock();
        $index->addEntity('wiki:syntax', ['dokuwiki']);
        $index->unlock();

        $len = strlen('dokuwiki');
        $this->assertEquals([$len => [0 => 0]], $index->getReverseAssignments('wiki:syntax'));
    }

    /**
     * resolveTokens should count frequencies and group by token length
     */
    public function testResolveTokens()
    {
        $index = new MockFrequencyCollection('rt_entity', 'rt_token', 'rt_freq', 'rt_reverse');
        $index->lock();

        $result = $this->callInaccessibleMethod($index, 'resolveTokens', [
            ['one', 'two', 'two', 'three'],
        ]);

        // 'one' and 'two' are 3 chars, 'three' is 5 chars
        $this->assertArrayHasKey(3, $result);
        $this->assertArrayHasKey(5, $result);

        // token IDs are sequential: one=0, two=1, three=0 (in its own length group)
        $this->assertEquals(1, $result[3][0]); // 'one' appears once
        $this->assertEquals(2, $result[3][1]); // 'two' appears twice
        $this->assertEquals(1, $result[5][0]); // 'three' appears once
    }

    /**
     * resolveTokens with empty input should return empty array
     */
    public function testResolveTokensEmpty()
    {
        $index = new MockFrequencyCollection('rte_entity', 'rte_token', 'rte_freq', 'rte_reverse');
        $index->lock();

        $result = $this->callInaccessibleMethod($index, 'resolveTokens', [[]]);

        $this->assertEmpty($result);
    }

    /**
     * countTokens should return occurrence counts
     */
    public function testCountTokens()
    {
        $index = new MockFrequencyCollection();

        $result = $this->callInaccessibleMethod($index, 'countTokens', [
            ['one', 'two', 'two', 'three', 'three', 'three'],
        ]);

        $this->assertEquals([
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ], $result);
    }

    /**
     * getEntitiesWithData on a split FrequencyCollection
     */
    public function testGetEntitiesWithData()
    {
        $index = new MockFrequencyCollection('ewd_page', 'ewd_w', 'ewd_i', 'ewd_pw');
        $index->lock();
        $index->addEntity('page1', ['dokuwiki', 'wiki']);
        $index->addEntity('page2', ['other', 'words']);
        $index->unlock();

        $result = $index->getEntitiesWithData();
        sort($result);
        $this->assertEquals(['page1', 'page2'], $result);
    }

    /**
     * getEntitiesWithData on an empty split collection returns empty array
     */
    public function testGetEntitiesWithDataEmpty()
    {
        $index = new MockFrequencyCollection('empty_page', 'empty_w', 'empty_i', 'empty_pw');
        $result = $index->getEntitiesWithData();
        $this->assertEquals([], $result);
    }

    /**
     * checkIntegrity on an empty split collection does not throw
     */
    public function testCheckIntegrityEmpty()
    {
        $index = new MockFrequencyCollection('ci_page', 'ci_w', 'ci_i', 'ci_pw');
        $index->checkIntegrity();
        $this->assertTrue(true); // no exception thrown
    }

    /**
     * checkIntegrity passes on a healthy split collection
     */
    public function testCheckIntegrityHealthy()
    {
        $index = new MockFrequencyCollection('cih_page', 'cih_w', 'cih_i', 'cih_pw');
        $index->lock();
        $index->addEntity('page1', ['dokuwiki', 'wiki']);
        $index->unlock();

        $index->checkIntegrity(); // should not throw
        $this->assertTrue(true);
    }

    /**
     * checkIntegrity detects missing frequency index for a group
     */
    public function testCheckIntegrityMissingFreqIndex()
    {
        global $conf;
        $index = new MockFrequencyCollection('cimf_page', 'cimf_w', 'cimf_i', 'cimf_pw');
        $index->lock();
        $index->addEntity('page1', ['dokuwiki', 'wiki']);
        $index->unlock();

        // find a group that exists and delete its frequency index
        $max = $index->getTokenIndexMaximum();
        @unlink($conf['indexdir'] . '/cimf_i' . $max . '.idx');

        $this->expectException(IndexIntegrityException::class);
        (new MockFrequencyCollection('cimf_page', 'cimf_w', 'cimf_i', 'cimf_pw'))->checkIntegrity();
    }

    /**
     * checkIntegrity detects missing token index for a group
     */
    public function testCheckIntegrityMissingTokenIndex()
    {
        global $conf;
        $index = new MockFrequencyCollection('cimt_page', 'cimt_w', 'cimt_i', 'cimt_pw');
        $index->lock();
        // use words of different lengths to create multiple groups
        $index->addEntity('page1', ['hi', 'dokuwiki', 'wiki']);
        $index->unlock();

        // delete the token index for the shortest group (not the max)
        @unlink($conf['indexdir'] . '/cimt_w2.idx');

        $this->expectException(IndexIntegrityException::class);
        (new MockFrequencyCollection('cimt_page', 'cimt_w', 'cimt_i', 'cimt_pw'))->checkIntegrity();
    }

    /**
     * groupToSuffix throws on group 0 for split collection
     */
    public function testGroupToSuffixValidationSplit()
    {
        $this->expectException(\dokuwiki\Search\Exception\IndexUsageException::class);

        $index = new MockFrequencyCollection('gs_page', 'gs_w', 'gs_i', 'gs_pw');
        // split collection should reject group 0
        $index->getTokenIndex(0);
    }
}
