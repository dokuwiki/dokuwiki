<?php

namespace dokuwiki\test\Search\Collection;

use dokuwiki\Search\Collection\Term;
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
        $term = new Term('');
        $this->assertEquals('', $term->getOriginal());
        $this->assertEquals('', $term->getBase());
        $this->assertEquals(0, $term->getLength());
    }

    public function testAddMatch()
    {
        $term = new Term('dokuwiki');

        $term->addMatch('page1', 'dokuwiki', 7);
        $term->addMatch('page1', 'dokuwiki', 7);
        $term->addMatch('page2', 'dokuwiki', 1);

        $this->assertEquals(['page1' => 14, 'page2' => 1], $term->getEntityFrequencies());
        $this->assertEquals(['dokuwiki'], $term->getTokens());
        $this->assertEquals(['page1' => ['dokuwiki'], 'page2' => ['dokuwiki']], $term->getEntityTokens());
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

    public function testShortTerm()
    {
        // Short terms are now accepted — length filtering is the caller's responsibility
        $term = new Term('a');
        $this->assertEquals('a', $term->getBase());
        $this->assertEquals(1, $term->getLength());
    }

    public function testOnlyWildcards()
    {
        // Wildcards-only terms are accepted but have an empty base
        $term = new Term('***');
        $this->assertEquals('', $term->getBase());
        $this->assertEquals(0, $term->getLength());
    }

    public function testFrequencyAggregationAcrossTokens()
    {
        // Simulate a search where term matches multiple tokens on the same entity
        $term = new Term('*wiki*');

        $term->addMatch('page1', 'wiki', 5);
        $term->addMatch('page1', 'dokuwiki', 3);
        $term->addMatch('page1', 'wikitext', 2);
        $term->addMatch('page2', 'wikipedia', 7);

        $frequencies = $term->getEntityFrequencies();
        $this->assertEquals(10, $frequencies['page1']); // 5 + 3 + 2
        $this->assertEquals(7, $frequencies['page2']);

        // getTokens returns all unique tokens
        $tokens = $term->getTokens();
        sort($tokens);
        $this->assertEquals(['dokuwiki', 'wiki', 'wikipedia', 'wikitext'], $tokens);

        // getEntityTokens returns tokens per entity
        $entityTokens = $term->getEntityTokens();
        $this->assertCount(3, $entityTokens['page1']);
        $this->assertEquals(['wikipedia'], $entityTokens['page2']);

        // getMatches returns full detail
        $matches = $term->getMatches();
        $this->assertEquals(['wiki' => 5, 'dokuwiki' => 3, 'wikitext' => 2], $matches['page1']);
        $this->assertEquals(['wikipedia' => 7], $matches['page2']);
    }

    public function testZeroFrequency()
    {
        $term = new Term('dokuwiki');

        $term->addMatch('page1', 'dokuwiki', 5);
        $term->addMatch('page2', 'dokuwiki', 0);
        $term->addMatch('page3', 'dokuwiki', 3);

        $frequencies = $term->getEntityFrequencies();
        $this->assertEquals(5, $frequencies['page1']);
        $this->assertEquals(0, $frequencies['page2']);
        $this->assertEquals(3, $frequencies['page3']);
    }

    public function testEmptyResults()
    {
        $term = new Term('dokuwiki');

        $this->assertEquals([], $term->getMatches());
        $this->assertEquals([], $term->getEntityFrequencies());
        $this->assertEquals([], $term->getEntityTokens());
        $this->assertEquals([], $term->getTokens());
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
