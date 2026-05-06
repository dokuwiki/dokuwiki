<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\PageTitleCollection;
use dokuwiki\Search\Exception\IndexIntegrityException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Index\MemoryIndex;

class DirectCollectionTest extends \DokuWikiTest
{
    /**
     * Add a token and verify it's stored at the entity's position
     */
    public function testAddEntity()
    {
        $index = new MockDirectCollection('a_entity', 'a_token');
        $index->lock();
        $index->addEntity('wiki:start', ['Welcome to DokuWiki']);
        $index->unlock();

        $idxEntity = new MemoryIndex('a_entity');
        $this->assertEquals('wiki:start', $idxEntity->retrieveRow(0));

        $idxToken = new MemoryIndex('a_token');
        $this->assertEquals('Welcome to DokuWiki', $idxToken->retrieveRow(0));
    }

    /**
     * Updating an entity should overwrite the previous token
     */
    public function testUpdateEntity()
    {
        $index = new MockDirectCollection('b_entity', 'b_token');

        $index->lock();
        $index->addEntity('wiki:start', ['Old Title']);
        $index->unlock();

        $index->lock();
        $index->addEntity('wiki:start', ['New Title']);
        $index->unlock();

        $idxToken = new MemoryIndex('b_token');
        $this->assertEquals('New Title', $idxToken->retrieveRow(0));
    }

    /**
     * Empty token list should store empty string
     */
    public function testEmptyToken()
    {
        $index = new MockDirectCollection('c_entity', 'c_token');
        $index->lock();
        $index->addEntity('wiki:start', []);
        $index->unlock();

        $idxToken = new MemoryIndex('c_token');
        $this->assertEquals('', $idxToken->retrieveRow(0));
    }

    /**
     * getToken should return the stored value
     */
    public function testGetToken()
    {
        $index = new MockDirectCollection('d_entity', 'd_token');
        $index->lock();
        $index->addEntity('wiki:start', ['My Page Title']);
        $index->unlock();

        $this->assertEquals('My Page Title', $index->getToken('wiki:start'));
    }

    /**
     * Adding entity without lock should throw exception
     */
    public function testAddEntityWithoutLock()
    {
        $this->expectException(IndexLockException::class);

        $index = new MockDirectCollection();
        $index->addEntity('wiki:start', ['Title']);
    }

    /**
     * Test that PageTitleCollection uses correct index names
     */
    public function testPageTitleCollection()
    {
        $index = new PageTitleCollection();
        $index->lock();
        $index->addEntity('wiki:start', ['Welcome']);
        $index->unlock();

        $idxToken = new MemoryIndex('title');
        $this->assertEquals('Welcome', $idxToken->retrieveRow(0));

        $this->assertEquals('Welcome', $index->getToken('wiki:start'));
    }

    /**
     * resolveTokenFrequencies maps token RID = entity RID with frequency 1
     */
    public function testResolveTokenFrequencies()
    {
        $index = new MockDirectCollection('rtf_entity', 'rtf_token');
        $index->lock();
        $index->addEntity('wiki:start', ['Title One']);
        $index->addEntity('wiki:syntax', ['Title Two']);
        $index->unlock();

        $result = $index->resolveTokenFrequencies(0, [0, 1]);
        $this->assertEquals([
            0 => [0 => 1],
            1 => [1 => 1],
        ], $result);
    }

    /**
     * getEntitiesWithData returns entities that have non-empty tokens
     */
    public function testGetEntitiesWithData()
    {
        $index = new MockDirectCollection('ewd_entity', 'ewd_token');
        $index->lock();
        $index->addEntity('wiki:start', ['Title One']);
        $index->addEntity('wiki:empty', []);
        $index->addEntity('wiki:syntax', ['Title Two']);
        $index->unlock();

        $result = $index->getEntitiesWithData();
        sort($result);
        $this->assertEquals(['wiki:start', 'wiki:syntax'], $result);
    }

    /**
     * checkIntegrity passes on a healthy DirectCollection
     */
    public function testCheckIntegrityHealthy()
    {
        $index = new MockDirectCollection('cih_entity', 'cih_token');
        $index->lock();
        $index->addEntity('wiki:start', ['Title One']);
        $index->unlock();

        $index->checkIntegrity(); // should not throw
        $this->assertTrue(true);
    }

    /**
     * checkIntegrity passes on an empty DirectCollection
     */
    public function testCheckIntegrityEmpty()
    {
        $index = new MockDirectCollection('cie_entity', 'cie_token');
        $index->checkIntegrity(); // should not throw
        $this->assertTrue(true);
    }

    /**
     * checkIntegrity detects entity/token line count mismatch
     */
    public function testCheckIntegrityMismatch()
    {
        global $conf;
        $index = new MockDirectCollection('cim_entity', 'cim_token');
        $index->lock();
        $index->addEntity('wiki:start', ['Title One']);
        $index->unlock();

        // corrupt: add extra line to token index
        file_put_contents($conf['indexdir'] . '/cim_token.idx', "extra\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new MockDirectCollection('cim_entity', 'cim_token'))->checkIntegrity();
    }
}
