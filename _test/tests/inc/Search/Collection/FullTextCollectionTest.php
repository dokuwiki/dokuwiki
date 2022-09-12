<?php

namespace tests\Search\Collection;

use dokuwiki\Search\Collection\FulltextCollection;
use dokuwiki\Search\Index\MemoryIndex;

class FullTextCollectionTest extends \DokuWikiTest
{

    /**
     * Add data and directly check the underlying indexes for correctness
     */
    public function testDirectly()
    {
        $index = new FulltextCollection('entity', 'token', 'freq', 'reverse');

        $tokens = ['one', 'two', 'three', 'four', 'two'];
        $index->addEntity('test', $tokens);

        $idxEntity = new MemoryIndex('entity');
        $this->assertEquals('test', $idxEntity->retrieveRow(0));

        $idxToken = new MemoryIndex('token','3');
        $this->assertEquals('one', $idxToken->retrieveRow(0));
        $this->assertEquals('two', $idxToken->retrieveRow(1));

        $idxFreq = new MemoryIndex('freq', '3');
        $this->assertEquals('0*1', $idxFreq->retrieveRow(0)); // one is 1x on page 0
        $this->assertEquals('0*2', $idxFreq->retrieveRow(1)); // two is 2x on page 0

        $idxRev = new MemoryIndex('reverse');
        $this->assertEquals('3*0:3*1:5*0:4*0', $idxRev->retrieveRow(0));

        // remove one of the tokens
        $tokens = ['two', 'three', 'four', 'two'];
        $index->addEntity('test', $tokens);

        $idxFreq = new MemoryIndex('freq', '3');
        $this->assertEquals('', $idxFreq->retrieveRow(0)); // one is not on page 0
    }

}
