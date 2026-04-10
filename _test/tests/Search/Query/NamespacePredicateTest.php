<?php

namespace dokuwiki\test\Search\Query;

use dokuwiki\Search\Query\NamespacePredicate;
use dokuwiki\Search\Query\PageSet;

class NamespacePredicateTest extends \DokuWikiTest
{
    protected PageSet $pages;

    public function setUp(): void
    {
        parent::setUp();
        $this->pages = new PageSet([
            'wiki:start' => 5,
            'wiki:syntax' => 3,
            'wiki:sub:page' => 2,
            'other:page' => 4,
            'other:deep:nested' => 1,
            'toplevel' => 6,
        ]);
    }

    public function testGetPrefix()
    {
        $ns = new NamespacePredicate('wiki:');
        $this->assertEquals('wiki:', $ns->getPrefix());
    }

    public function testFilterKeepsMatchingNamespace()
    {
        $ns = new NamespacePredicate('wiki:');
        $result = $ns->filter($this->pages);

        $this->assertEquals([
            'wiki:start' => 5,
            'wiki:syntax' => 3,
            'wiki:sub:page' => 2,
        ], $result->getPages());
    }

    public function testFilterIncludesSubNamespaces()
    {
        $ns = new NamespacePredicate('wiki:');
        $result = $ns->filter($this->pages);

        $this->assertArrayHasKey('wiki:sub:page', $result->getPages());
    }

    public function testFilterDoesNotMatchPartialPrefix()
    {
        // "other:" must not match "otherstuff:page"
        $pages = new PageSet([
            'other:page' => 1,
            'otherstuff:page' => 2,
        ]);
        $ns = new NamespacePredicate('other:');
        $result = $ns->filter($pages);

        $this->assertEquals(['other:page' => 1], $result->getPages());
    }

    public function testExcludeRemovesMatchingNamespace()
    {
        $ns = new NamespacePredicate('wiki:');
        $result = $ns->exclude($this->pages);

        $this->assertEquals([
            'other:page' => 4,
            'other:deep:nested' => 1,
            'toplevel' => 6,
        ], $result->getPages());
    }

    public function testFilterOnEmptyPageSet()
    {
        $ns = new NamespacePredicate('wiki:');
        $result = $ns->filter(new PageSet());

        $this->assertEquals([], $result->getPages());
    }

    public function testFilterNoMatch()
    {
        $ns = new NamespacePredicate('nonexistent:');
        $result = $ns->filter($this->pages);

        $this->assertEquals([], $result->getPages());
    }

    public function testExcludeNoMatch()
    {
        $ns = new NamespacePredicate('nonexistent:');
        $result = $ns->exclude($this->pages);

        // all pages should remain
        $this->assertEquals($this->pages->getPages(), $result->getPages());
    }

    public function testPreservesScores()
    {
        $ns = new NamespacePredicate('other:');
        $result = $ns->filter($this->pages);

        $this->assertEquals(4, $result->getPages()['other:page']);
        $this->assertEquals(1, $result->getPages()['other:deep:nested']);
    }
}
