<?php

namespace tests\Search\Collection;

use dokuwiki\Search\Collection\FulltextCollection;
use dokuwiki\Search\Collection\FulltextCollectionSearch;
use dokuwiki\Search\Index\MemoryIndex;
use dokuwiki\Search\QueryParser;
use dokuwiki\Search\Tokenizer;

class FullTextCollectionSearchTest extends \DokuWikiTest
{

    public function testExactTerm()
    {
        // add some content to the indexes
        $collection = new FulltextCollection('page', 'w', 'i', 'pageword');
        $collection->lock();
        $collection->addEntity('page1', ['dokuwiki', 'dokuwiki', 'dokuwikis', 'doku', 'wiki']);
        $collection->addEntity('page2', ['dokuwiki', 'other', 'words']);
        $collection->unlock();

        // add search term
        $search = new FulltextCollectionSearch($collection);
        $term = $search->addTerm('dokuwiki');

        // execute search
        $search->execute();

        // inspect the term updates first:

        // exact search should only match one token
        $this->assertEquals(['dokuwiki'],  $term->getTokens());
        // that token is 8 chars and should be the first in the index
        $this->assertEquals([0], $term->getTokenIDsByLength(8));
        // the dokuwiki token is two times on page1 and 1 time on page2
        $this->assertEquals(['page1' => 2, 'page2' => 1], $term->getEntityFrequencies());

        // now get data from the collectionSearch:

        // entity IDs should be available
        $this->assertEquals([0 => 'page1', 1 => 'page2'], $search->getEntities());

    }

    public function xxxRealWord()
    {
        $tokens = Tokenizer::getWords(rawWiki('wiki:syntax'));
        $collection = new FulltextCollection('page', 'word', 'w', 'pageword');
        $collection->addEntity('wiki:syntax', $tokens);

        $search = new FulltextCollectionSearch($collection);

        $search->addTerm('dokuwiki');
        $search->addTerm('*wiki');
        $search->addTerm('doku*');
        $search->addTerm('*kuwi*');

        $result = $search->execute();

        $this->assertEquals([], $result);
    }


}
