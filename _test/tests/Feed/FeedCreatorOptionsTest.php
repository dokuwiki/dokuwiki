<?php

namespace dokuwiki\test\Feed;

use dokuwiki\Feed\FeedCreatorOptions;

class FeedCreatorOptionsTest extends \DokuWikiTest
{
    // region getCacheKey mode behavior

    public function testRecentModeIsCacheable()
    {
        $options = new FeedCreatorOptions(['feed_mode' => 'recent']);
        $this->assertNotNull($options->getCacheKey());
    }

    public function testListModeIsNotCacheable()
    {
        $options = new FeedCreatorOptions(['feed_mode' => 'list', 'namespace' => 'wiki']);
        $this->assertNull($options->getCacheKey());
    }

    public function testSearchModeIsNotCacheable()
    {
        $options = new FeedCreatorOptions(['feed_mode' => 'search', 'search_query' => 'test']);
        $this->assertNull($options->getCacheKey());
    }

    public function testUnknownModeIsNotCacheable()
    {
        $options = new FeedCreatorOptions(['feed_mode' => 'foobar']);
        $this->assertNull($options->getCacheKey());
    }

    // endregion

    // region irrelevant params don't affect recent key

    public function testSortDoesNotAffectRecentKey()
    {
        $a = new FeedCreatorOptions(['feed_mode' => 'recent', 'sort' => 'natural']);
        $b = new FeedCreatorOptions(['feed_mode' => 'recent', 'sort' => 'date']);
        $this->assertSame($a->getCacheKey(), $b->getCacheKey());
    }

    public function testSearchQueryDoesNotAffectRecentKey()
    {
        $a = new FeedCreatorOptions(['feed_mode' => 'recent']);
        $b = new FeedCreatorOptions(['feed_mode' => 'recent', 'search_query' => 'anything']);
        $this->assertSame($a->getCacheKey(), $b->getCacheKey());
    }

    // endregion

    // region relevant params DO affect recent key

    public function testNamespaceAffectsRecentKey()
    {
        $a = new FeedCreatorOptions(['feed_mode' => 'recent', 'namespace' => '']);
        $b = new FeedCreatorOptions(['feed_mode' => 'recent', 'namespace' => 'wiki']);
        $this->assertNotSame($a->getCacheKey(), $b->getCacheKey());
    }

    public function testItemsAffectsRecentKey()
    {
        $a = new FeedCreatorOptions(['feed_mode' => 'recent', 'items' => 10]);
        $b = new FeedCreatorOptions(['feed_mode' => 'recent', 'items' => 20]);
        $this->assertNotSame($a->getCacheKey(), $b->getCacheKey());
    }

    public function testShowMinorAffectsRecentKey()
    {
        $a = new FeedCreatorOptions(['feed_mode' => 'recent', 'show_minor' => false]);
        $b = new FeedCreatorOptions(['feed_mode' => 'recent', 'show_minor' => true]);
        $this->assertNotSame($a->getCacheKey(), $b->getCacheKey());
    }

    public function testContentTypeAffectsRecentKey()
    {
        $a = new FeedCreatorOptions(['feed_mode' => 'recent', 'content_type' => 'pages']);
        $b = new FeedCreatorOptions(['feed_mode' => 'recent', 'content_type' => 'both']);
        $this->assertNotSame($a->getCacheKey(), $b->getCacheKey());
    }

    // endregion

    // region items clamping

    public function testItemsClampedToMinimumOne()
    {
        $options = new FeedCreatorOptions(['items' => 0]);
        $this->assertSame(1, $options->options['items']);
    }

    public function testItemsClampedToMaximum()
    {
        global $conf;
        $options = new FeedCreatorOptions(['items' => 999999]);
        $this->assertSame($conf['recent'] * 5, $options->options['items']);
    }

    public function testItemsValidValuePassesThrough()
    {
        $options = new FeedCreatorOptions(['items' => 10]);
        $this->assertSame(10, $options->options['items']);
    }

    // endregion
}
