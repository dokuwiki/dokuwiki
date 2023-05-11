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
    public function basicExact()
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

    public function testBadTerm()
    {
        $this->expectException(SearchException::class);
        $this->expectDeprecationMessageMatches('/short/i');
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

}
