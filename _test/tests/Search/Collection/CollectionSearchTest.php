<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\CollectionSearch;
use dokuwiki\Search\Index\MemoryIndex;
use dokuwiki\Search\Tokenizer;

class CollectionSearchTest extends \DokuWikiTest
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
        $search = new CollectionSearch($collection);
        $term = $search->addTerm('dokuwiki');

        // execute search
        $search->execute();

        // exact search should only match one token
        $this->assertEquals(['dokuwiki'], $term->getTokens());
        // the dokuwiki token is two times on page1 and 1 time on page2
        $this->assertEquals(['page1' => 2, 'page2' => 1], $term->getEntityFrequencies());
        // full detail available
        $this->assertEquals(['dokuwiki' => 2], $term->getMatches()['page1']);
        $this->assertEquals(['dokuwiki' => 1], $term->getMatches()['page2']);

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

        $search = new CollectionSearch($collection);
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

        $search = new CollectionSearch($collection);
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

        $search = new CollectionSearch($collection);
        $term = $search->addTerm('zzzznotfound');
        $search->execute();

        $this->assertEmpty($term->getTokens());
        $this->assertEmpty($term->getEntityFrequencies());
        $this->assertEmpty($term->getMatches());
    }

    // --- metadata-style search tests (using addTerm/execute without length restrictions) ---

    /**
     * Exact search on a non-split LookupCollection
     */
    public function testMetadataExact()
    {
        $collection = new MockLookupCollection('le_entity', 'le_token', 'le_freq', 'le_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $collection->addEntity('wiki:other', ['wiki:syntax']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $term = $search->addTerm('wiki:syntax');
        $search->execute();

        $pages = array_keys($term->getEntityFrequencies());
        sort($pages);
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages);
    }

    /**
     * Wildcard search on a non-split LookupCollection
     */
    public function testMetadataWildcard()
    {
        $collection = new MockLookupCollection('lw_entity', 'lw_token', 'lw_freq', 'lw_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $collection->addEntity('wiki:other', ['wiki:syntax', 'other:page']);
        $collection->unlock();

        // end wildcard: wiki:* matches wiki:syntax and wiki:welcome
        $search = new CollectionSearch($collection);
        $term = $search->addTerm('wiki:*');
        $search->execute();

        $pages = array_keys($term->getEntityFrequencies());
        sort($pages);
        // wiki:start has both tokens (freq 2), wiki:other has wiki:syntax (freq 1)
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages);

        // start wildcard: *syntax matches only wiki:syntax
        $search2 = new CollectionSearch($collection);
        $term2 = $search2->addTerm('*syntax');
        $search2->execute();

        $pages2 = array_keys($term2->getEntityFrequencies());
        sort($pages2);
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages2);
    }

    /**
     * Case-insensitive search on a non-split LookupCollection
     */
    public function testMetadataCaseInsensitive()
    {
        $collection = new MockLookupCollection('lc_entity', 'lc_token', 'lc_freq', 'lc_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['Apple', 'Banana']);
        $collection->addEntity('wiki:other', ['Cherry', 'Apple Pie']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $search->caseInsensitive();
        $term = $search->addTerm('*apple*');
        $search->execute();

        $pages = array_keys($term->getEntityFrequencies());
        sort($pages);
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages);
    }

    /**
     * Search on a DirectCollection (title-style 1:1 mapping)
     */
    public function testSearchOnDirectCollection()
    {
        $collection = new MockDirectCollection('ld_entity', 'ld_token');
        $collection->lock();
        $collection->addEntity('wiki:start', ['Welcome to DokuWiki']);
        $collection->addEntity('wiki:syntax', ['Formatting Syntax']);
        $collection->addEntity('wiki:other', ['Other Page']);
        $collection->unlock();

        // exact match
        $search = new CollectionSearch($collection);
        $term = $search->addTerm('Welcome to DokuWiki');
        $search->execute();
        $this->assertEquals(['wiki:start'], array_keys($term->getEntityFrequencies()));

        // wildcard match
        $search2 = new CollectionSearch($collection);
        $term2 = $search2->addTerm('*Syntax');
        $search2->execute();
        $this->assertEquals(['wiki:syntax'], array_keys($term2->getEntityFrequencies()));

        // case-insensitive substring match
        $search3 = new CollectionSearch($collection);
        $search3->caseInsensitive();
        $term3 = $search3->addTerm('*wiki*');
        $search3->execute();
        $this->assertEquals(['wiki:start'], array_keys($term3->getEntityFrequencies()));
    }

    /**
     * Multiple terms in a single search
     */
    public function testMultipleTerms()
    {
        $collection = new MockLookupCollection('lm_entity', 'lm_token', 'lm_freq', 'lm_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $collection->addEntity('wiki:other', ['wiki:syntax']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $term1 = $search->addTerm('wiki:syntax');
        $term2 = $search->addTerm('wiki:welcome');
        $term3 = $search->addTerm('nonexistent');
        $search->execute();

        $syntax = array_keys($term1->getEntityFrequencies());
        sort($syntax);
        $this->assertEquals(['wiki:other', 'wiki:start'], $syntax);
        $this->assertEquals(['wiki:start'], array_keys($term2->getEntityFrequencies()));
        $this->assertEquals([], array_keys($term3->getEntityFrequencies()));
    }

    /**
     * Search on a split FrequencyCollection
     */
    public function testSearchOnSplitCollection()
    {
        $collection = new MockFrequencyCollection('ls_page', 'ls_w', 'ls_i', 'ls_pageword');
        $collection->lock();
        $collection->addEntity('page1', ['dokuwiki', 'wiki', 'doku']);
        $collection->addEntity('page2', ['dokuwiki', 'other']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $term = $search->addTerm('dokuwiki');
        $search->execute();

        $pages = array_keys($term->getEntityFrequencies());
        sort($pages);
        $this->assertEquals(['page1', 'page2'], $pages);
    }

    /**
     * Searching an empty collection returns no results
     */
    public function testSearchEmptyCollection()
    {
        $collection = new MockFrequencyCollection('empty_page', 'empty_w', 'empty_i', 'empty_pw');

        $search = new CollectionSearch($collection);
        $term = $search->addTerm('anything');
        $search->execute();
        $this->assertEquals([], $term->getEntityFrequencies());
    }

    /**
     * Search on an empty collection returns empty frequencies
     */
    public function testSearchEmptyCollection2()
    {
        $collection = new MockFrequencyCollection('empty2_page', 'empty2_w', 'empty2_i', 'empty2_pw');

        $search = new CollectionSearch($collection);
        $term = $search->addTerm('anything');
        $search->execute();
        $this->assertEquals([], $term->getEntityFrequencies());
    }
}
