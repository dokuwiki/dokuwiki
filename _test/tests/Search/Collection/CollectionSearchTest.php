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

        // inspect the term updates first:

        // exact search should only match one token
        $this->assertEquals(['dokuwiki'],  $term->getTokens());
        // that token is 8 chars and should be the first in the index
        $this->assertEquals([0], $term->getTokenIDsByGroup(8));
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
        $this->assertEmpty($search->getEntities());
    }

    // --- lookup() tests ---

    /**
     * Exact lookup on a non-split LookupCollection
     */
    public function testLookupExact()
    {
        $collection = new MockLookupCollection('le_entity', 'le_token', 'le_freq', 'le_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $collection->addEntity('wiki:other', ['wiki:syntax']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $result = $search->lookup('wiki:syntax');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('wiki:syntax', $result);
        $pages = $result['wiki:syntax'];
        sort($pages);
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages);
    }

    /**
     * Wildcard lookup on a non-split LookupCollection
     */
    public function testLookupWildcard()
    {
        $collection = new MockLookupCollection('lw_entity', 'lw_token', 'lw_freq', 'lw_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $collection->addEntity('wiki:other', ['wiki:syntax', 'other:page']);
        $collection->unlock();

        $search = new CollectionSearch($collection);

        // end wildcard: wiki:* matches wiki:syntax and wiki:welcome
        // wiki:start has both tokens, so it appears twice; wiki:other has wiki:syntax once
        $result = $search->lookup('wiki:*');
        $pages = $result['wiki:*'];
        sort($pages);
        $this->assertEquals(['wiki:other', 'wiki:start', 'wiki:start'], $pages);

        // start wildcard: *syntax matches only wiki:syntax
        $search2 = new CollectionSearch($collection);
        $result2 = $search2->lookup('*syntax');
        $pages2 = $result2['*syntax'];
        sort($pages2);
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages2);
    }

    /**
     * Callback lookup on a non-split LookupCollection
     */
    public function testLookupCallback()
    {
        $collection = new MockLookupCollection('lc_entity', 'lc_token', 'lc_freq', 'lc_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['Apple', 'Banana']);
        $collection->addEntity('wiki:other', ['Cherry', 'Apple Pie']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        // case-insensitive substring match
        $result = $search->lookup('apple', static fn($search, $word) => stripos($word, $search) !== false);

        $pages = $result['apple'];
        sort($pages);
        $this->assertEquals(['wiki:other', 'wiki:start'], $pages);
    }

    /**
     * lookup() on a DirectCollection (title-style 1:1 mapping)
     */
    public function testLookupOnDirectCollection()
    {
        $collection = new MockDirectCollection('ld_entity', 'ld_token');
        $collection->lock();
        $collection->addEntity('wiki:start', ['Welcome to DokuWiki']);
        $collection->addEntity('wiki:syntax', ['Formatting Syntax']);
        $collection->addEntity('wiki:other', ['Other Page']);
        $collection->unlock();

        $search = new CollectionSearch($collection);

        // exact match
        $result = $search->lookup('Welcome to DokuWiki');
        $this->assertEquals(['wiki:start'], $result['Welcome to DokuWiki']);

        // wildcard match
        $search2 = new CollectionSearch($collection);
        $result2 = $search2->lookup('*Syntax');
        $this->assertEquals(['wiki:syntax'], $result2['*Syntax']);

        // callback match (case-insensitive substring)
        $search3 = new CollectionSearch($collection);
        $result3 = $search3->lookup('wiki', static fn($s, $w) => stripos($w, $s) !== false);
        $this->assertEquals(['wiki:start'], $result3['wiki']);
    }

    /**
     * lookup() with multiple values
     */
    public function testLookupMultipleValues()
    {
        $collection = new MockLookupCollection('lm_entity', 'lm_token', 'lm_freq', 'lm_reverse');
        $collection->lock();
        $collection->addEntity('wiki:start', ['wiki:syntax', 'wiki:welcome']);
        $collection->addEntity('wiki:other', ['wiki:syntax']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $result = $search->lookup(['wiki:syntax', 'wiki:welcome', 'nonexistent']);

        $syntax = $result['wiki:syntax'];
        sort($syntax);
        $this->assertEquals(['wiki:other', 'wiki:start'], $syntax);
        $this->assertEquals(['wiki:start'], $result['wiki:welcome']);
        $this->assertEquals([], $result['nonexistent']);
    }

    /**
     * lookup() on a split FrequencyCollection
     */
    public function testLookupOnSplitCollection()
    {
        $collection = new MockFrequencyCollection('ls_page', 'ls_w', 'ls_i', 'ls_pageword');
        $collection->lock();
        $collection->addEntity('page1', ['dokuwiki', 'wiki', 'doku']);
        $collection->addEntity('page2', ['dokuwiki', 'other']);
        $collection->unlock();

        $search = new CollectionSearch($collection);
        $result = $search->lookup('dokuwiki');

        $pages = $result['dokuwiki'];
        sort($pages);
        $this->assertEquals(['page1', 'page2'], $pages);
    }
}
