<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\PageTitleCollection;
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
}
