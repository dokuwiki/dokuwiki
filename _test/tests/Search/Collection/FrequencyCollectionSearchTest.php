<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\FrequencyCollectionSearch;
use dokuwiki\Search\Index\MemoryIndex;
use dokuwiki\Search\Tokenizer;

class FrequencyCollectionSearchTest extends \DokuWikiTest
{

    public function testExactTerm()
    {
        // add some content to the indexes
        $collection = new MockFrequencyCollection('page', 'w', 'i', 'pageword');
        $collection->lock();
        $collection->addEntity('page1', ['dokuwiki', 'dokuwiki', 'dokuwikis', 'doku', 'wiki']);
        $collection->addEntity('page2', ['dokuwiki', 'other', 'words']);
        $collection->unlock();

        // add search term
        $search = new FrequencyCollectionSearch($collection);
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

        // entity IDs should be available from the search
        $this->assertEquals([0 => 'page1', 1 => 'page2'], $search->getEntities());

    }

    public function testWildcardSearch()
    {
        // page1 has: dokuwiki(x2), dokuwikis, doku, wiki
        // page2 has: dokuwiki, other, words
        $collection = new MockFrequencyCollection('wc_page', 'wc_w', 'wc_i', 'wc_pageword');
        $collection->lock();
        $collection->addEntity('page1', ['dokuwiki', 'dokuwiki', 'dokuwikis', 'doku', 'wiki']);
        $collection->addEntity('page2', ['dokuwiki', 'other', 'words']);
        $collection->unlock();

        $search = new FrequencyCollectionSearch($collection);
        $endWild = $search->addTerm('doku*');
        $startWild = $search->addTerm('*wiki');
        $bothWild = $search->addTerm('*kuwi*');
        $search->execute();

        // doku* should match: doku(4), dokuwiki(8), dokuwikis(9)
        $endTokens = $endWild->getTokens();
        sort($endTokens);
        $this->assertEquals(['doku', 'dokuwiki', 'dokuwikis'], $endTokens);
        // page1 has doku(1) + dokuwiki(2) + dokuwikis(1) = 4, page2 has dokuwiki(1) = 1
        $this->assertEquals(['page1' => 4, 'page2' => 1], $endWild->getEntityFrequencies());

        // *wiki should match: dokuwiki(8), wiki(4)
        $startTokens = $startWild->getTokens();
        sort($startTokens);
        $this->assertEquals(['dokuwiki', 'wiki'], $startTokens);
        // page1 has dokuwiki(2) + wiki(1) = 3, page2 has dokuwiki(1) = 1
        $this->assertEquals(['page1' => 3, 'page2' => 1], $startWild->getEntityFrequencies());

        // *kuwi* should match: dokuwiki(8), dokuwikis(9)
        $bothTokens = $bothWild->getTokens();
        sort($bothTokens);
        $this->assertEquals(['dokuwiki', 'dokuwikis'], $bothTokens);
        // page1 has dokuwiki(2) + dokuwikis(1) = 3, page2 has dokuwiki(1) = 1
        $this->assertEquals(['page1' => 3, 'page2' => 1], $bothWild->getEntityFrequencies());
    }

    /**
     * Index a real text file via the Tokenizer and search it
     */
    public function testTokenizedPageSearch()
    {
        $text = file_get_contents(__DIR__ . '/../data/searchtest.txt');
        $tokens = Tokenizer::getWords($text);

        $collection = new MockFrequencyCollection('tp_page', 'tp_w', 'tp_i', 'tp_pageword');
        $collection->lock();
        $collection->addEntity('search:test', $tokens);
        $collection->unlock();

        $search = new FrequencyCollectionSearch($collection);
        $exact = $search->addTerm('dokuwiki');
        $wild = $search->addTerm('plugin*');
        $search->execute();

        // "dokuwiki" appears 4 times in the text (case-insensitive tokenization)
        $this->assertEquals(['dokuwiki'], $exact->getTokens());
        $this->assertEquals(['search:test' => 4], $exact->getEntityFrequencies());

        // "plugin*" should match "plugins" (7 chars) and "plugin" would be too if present
        $wildTokens = $wild->getTokens();
        $this->assertContains('plugins', $wildTokens);
        $this->assertNotEmpty($wild->getEntityFrequencies());
        $this->assertArrayHasKey('search:test', $wild->getEntityFrequencies());
    }

    public function testNoMatchReturnsEmptyFrequencies()
    {
        $collection = new MockFrequencyCollection('nm_page', 'nm_w', 'nm_i', 'nm_pageword');
        $collection->lock();
        $collection->addEntity('page1', ['alpha', 'beta', 'gamma']);
        $collection->unlock();

        $search = new FrequencyCollectionSearch($collection);
        $term = $search->addTerm('zzzznotfound');
        $search->execute();

        $this->assertEmpty($term->getTokens());
        $this->assertEmpty($term->getEntityFrequencies());
        $this->assertEmpty($search->getEntities());
    }
}
