<?php

namespace tests\Search\Collection;

use dokuwiki\Search\Collection\FulltextCollection;
use dokuwiki\Search\Collection\FulltextCollectionSearch;
use dokuwiki\Search\Collection\Term;
use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Index\MemoryIndex;
use dokuwiki\Search\QueryParser;
use dokuwiki\Search\Tokenizer;

class TermTest extends \DokuWikiTest
{
    public function testBasicExact()
    {
        $term = new Term('dokuwiki');

        $this->assertEquals('dokuwiki', $term->getOriginal());
        $this->assertEquals('dokuwiki', $term->getBase());
        $this->assertEquals('dokuwiki', $term->getQuoted());
        $this->assertEquals(8, $term->getLength());
        $this->assertEquals(Term::WILDCARD_NONE, $term->getWildcard());
    }

    public function testBasicLeftWildcard()
    {
        $term = new Term('*wiki');

        $this->assertEquals('*wiki', $term->getOriginal());
        $this->assertEquals('wiki', $term->getBase());
        $this->assertEquals('.*wiki', $term->getQuoted());
        $this->assertEquals(4, $term->getLength());
        $this->assertEquals(Term::WILDCARD_START, $term->getWildcard());
    }

    public function testBasicRightWildcard()
    {
        $term = new Term('wiki*');

        $this->assertEquals('wiki*', $term->getOriginal());
        $this->assertEquals('wiki', $term->getBase());
        $this->assertEquals('wiki.*', $term->getQuoted());
        $this->assertEquals(4, $term->getLength());
        $this->assertEquals(Term::WILDCARD_END, $term->getWildcard());
    }

    public function testBasicBothWildcard()
    {
        $term = new Term('*wiki*');

        $this->assertEquals('*wiki*', $term->getOriginal());
        $this->assertEquals('wiki', $term->getBase());
        $this->assertEquals('.*wiki.*', $term->getQuoted());
        $this->assertEquals(4, $term->getLength());
        $this->assertEquals(Term::WILDCARD_START + Term::WILDCARD_END, $term->getWildcard());
    }

    public function testEmptyTerm()
    {
        $this->expectException(SearchException::class);
        $this->expectExceptionMessageMatches('/short/i');
        new Term('');
    }

    public function testTokenAdding()
    {
        $term = new Term('*wiki*');
        $term->addTokens(8, [0 => 'dokuwiki']);
        $term->addTokens(5, [0 => 'wikis', 134 => 'awiki']);

        $this->assertEquals(['dokuwiki', 'wikis', 'awiki'], $term->getTokens());

        $this->assertEquals([0], $term->getTokenIDsByLength(8));
        $this->assertEquals([0, 134], $term->getTokenIDsByLength(5));
        $this->assertEquals([], $term->getTokenIDsByLength(3));
    }

    public function testFrequencyAdding()
    {
        $term = new Term('dokuwiki');

        $term->addEntityFrequency(7, 7);
        $term->addEntityFrequency(7, 7);
        $term->addEntityFrequency(8, 1);

        $this->assertEquals([7 => 14, 8 => 1], $term->getEntityFrequencies());

        $map = [
            7 => 'page1',
            8 => 'page2'
        ];
        $term->resolveEntities($map);

        $this->assertEquals(['page1' => 14, 'page2' => 1], $term->getEntityFrequencies());
    }

    public function testNumericTerm()
    {
        // Numeric terms should be allowed even if they're shorter than minimum word length
        $term = new Term('42');

        $this->assertEquals('42', $term->getOriginal());
        $this->assertEquals('42', $term->getBase());
        $this->assertEquals(2, $term->getLength());
        $this->assertEquals(Term::WILDCARD_NONE, $term->getWildcard());
    }

    public function testSpecialCharactersQuoting()
    {
        // Test that special regex characters are properly escaped
        $term = new Term('test.doc');

        $this->assertEquals('test.doc', $term->getOriginal());
        $this->assertEquals('test.doc', $term->getBase());
        // The dot should be escaped in the quoted version
        $this->assertEquals('test\\.doc', $term->getQuoted());
    }

    public function testSpecialCharactersWithWildcard()
    {
        // Test special chars with wildcard
        $term = new Term('test.*');

        $this->assertEquals('test.*', $term->getOriginal());
        $this->assertEquals('test.', $term->getBase());
        // The dot should be escaped, but the wildcard * should become .*
        $this->assertEquals('test\\..*', $term->getQuoted());
        $this->assertEquals(Term::WILDCARD_END, $term->getWildcard());
    }

    public function testWildcardTrimming()
    {
        // Test that only wildcards (not spaces) are trimmed from base
        $term = new Term('*wiki*');

        $this->assertEquals('*wiki*', $term->getOriginal());
        $this->assertEquals('wiki', $term->getBase());
        $this->assertEquals('.*wiki.*', $term->getQuoted());
        $this->assertEquals(Term::WILDCARD_START + Term::WILDCARD_END, $term->getWildcard());
    }

    public function testTooShortTerm()
    {
        // Get the minimum word length
        $minLength = Tokenizer::getMinWordLength();

        if ($minLength > 1) {
            $this->expectException(SearchException::class);
            $this->expectExceptionMessageMatches('/short/i');
            // Create a term that's too short (one character less than minimum)
            new Term(str_repeat('a', $minLength - 1));
        } else {
            // If minimum length is 1 or less, this test doesn't apply
            $this->markTestSkipped('Minimum word length is too small for this test');
        }
    }

    public function testOnlyWildcards()
    {
        $this->expectException(SearchException::class);
        $this->expectExceptionMessageMatches('/short/i');
        new Term('***');
    }

    public function testMultipleLengthTokens()
    {
        $term = new Term('*wiki*');

        // Add tokens of various lengths
        $term->addTokens(4, [10 => 'wiki', 11 => 'mwiki']);
        $term->addTokens(8, [20 => 'dokuwiki', 21 => 'pmwiki']);
        $term->addTokens(9, [30 => 'mediawiki']);

        // Check we get all tokens
        $allTokens = $term->getTokens();
        $this->assertCount(5, $allTokens);
        $this->assertContains('wiki', $allTokens);
        $this->assertContains('dokuwiki', $allTokens);
        $this->assertContains('mediawiki', $allTokens);

        // Check we can get tokens by specific length
        $this->assertEquals([10, 11], $term->getTokenIDsByLength(4));
        $this->assertEquals([20, 21], $term->getTokenIDsByLength(8));
        $this->assertEquals([30], $term->getTokenIDsByLength(9));
        $this->assertEquals([], $term->getTokenIDsByLength(5));
    }

    public function testFrequencyAggregationAcrossTokens()
    {
        // Simulate a search where term matches multiple tokens on the same entity
        $term = new Term('*wiki*');

        // Entity 1 has multiple matching tokens
        $term->addEntityFrequency(1, 5);  // first token appears 5 times
        $term->addEntityFrequency(1, 3);  // second token appears 3 times
        $term->addEntityFrequency(1, 2);  // third token appears 2 times

        // Entity 2 has one matching token
        $term->addEntityFrequency(2, 7);

        $frequencies = $term->getEntityFrequencies();
        $this->assertEquals(10, $frequencies[1]); // 5 + 3 + 2
        $this->assertEquals(7, $frequencies[2]);
    }

    public function testEmptyTokensByLength()
    {
        $term = new Term('dokuwiki');

        // Before adding any tokens, getting by length should return empty
        $this->assertEquals([], $term->getTokenIDsByLength(8));

        // After adding tokens, querying a non-existent length returns empty
        $term->addTokens(4, [10 => 'wiki']);
        $this->assertEquals([], $term->getTokenIDsByLength(8));
    }

    public function testZeroFrequency()
    {
        $term = new Term('dokuwiki');

        $term->addEntityFrequency(1, 5);
        $term->addEntityFrequency(2, 0);  // Zero frequency
        $term->addEntityFrequency(3, 3);

        $frequencies = $term->getEntityFrequencies();
        $this->assertEquals(5, $frequencies[1]);
        $this->assertEquals(0, $frequencies[2]);  // Zero is stored
        $this->assertEquals(3, $frequencies[3]);
    }

    public function testResolveEntitiesPartialMap()
    {
        $term = new Term('dokuwiki');

        $term->addEntityFrequency(1, 5);
        $term->addEntityFrequency(2, 3);

        // Resolve with partial map - only some entities are mapped
        $map = [
            1 => 'page1',
            2 => 'page2'
        ];
        $term->resolveEntities($map);

        $frequencies = $term->getEntityFrequencies();
        $this->assertEquals(5, $frequencies['page1']);
        $this->assertEquals(3, $frequencies['page2']);
        $this->assertCount(2, $frequencies);
    }

    public function testCaseSensitiveBase()
    {
        // Test that case is preserved
        $term = new Term('DokuWiki');

        $this->assertEquals('DokuWiki', $term->getOriginal());
        $this->assertEquals('DokuWiki', $term->getBase());
    }

    public function testComplexRegexCharacters()
    {
        // Test multiple special regex characters
        $term = new Term('test[0-9]+.txt');

        $this->assertEquals('test[0-9]+.txt', $term->getOriginal());
        $this->assertEquals('test[0-9]+.txt', $term->getBase());
        // All special characters should be escaped
        $quoted = $term->getQuoted();
        $this->assertStringContainsString('\\[', $quoted);
        $this->assertStringContainsString('\\]', $quoted);
        $this->assertStringContainsString('\\+', $quoted);
        $this->assertStringContainsString('\\.', $quoted);
    }

}
