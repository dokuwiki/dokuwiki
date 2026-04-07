<?php

namespace dokuwiki\test\Search;

use dokuwiki\Search\Indexer;
use dokuwiki\Search\MetadataSearch;

/**
 * Tests for MetadataSearch utility methods
 */
class MetadataSearchTest extends \DokuWikiTest
{
    /**
     * pageLookup finds pages by name, respects namespace filters, and searches titles with UTF-8
     */
    public function testPageLookup()
    {
        $indexer = new Indexer();
        $search = new MetadataSearch();

        saveWikiText('test:page1', 'Some text', 'Test initialization');
        $indexer->addPage('test:page1', true);
        saveWikiText('ns:page2', 'Other text', 'Test initialization');
        $indexer->addPage('ns:page2', true);
        saveWikiText('ns:utf8', '====== Title with ÄöÜ ======', 'Test initialization');
        $indexer->addPage('ns:utf8', true);

        $this->assertEquals(['test:page1' => null, 'ns:page2' => null], $search->pageLookup('page'));
        $this->assertEquals(['test:page1' => null], $search->pageLookup('page @test'));
        $this->assertEquals(['ns:page2' => null], $search->pageLookup('page ^test'));

        $this->assertEquals(['ns:utf8' => 'Title with ÄöÜ'], $search->pageLookup('title', false, true));
        $this->assertEquals(['ns:utf8' => 'Title with ÄöÜ'], $search->pageLookup('äöü', false, true));
    }

    /**
     * filterPages removes non-existent pages
     */
    public function testFilterPagesRemovesNonExistent()
    {
        saveWikiText('wiki:existing', 'content', 'init');

        $pages = ['wiki:existing' => true, 'wiki:nonexistent' => true];
        $result = MetadataSearch::filterPages($pages);

        $this->assertArrayHasKey('wiki:existing', $result);
        $this->assertArrayNotHasKey('wiki:nonexistent', $result);
    }

    /**
     * filterPages respects hidden pages setting
     */
    public function testFilterPagesHidden()
    {
        global $conf;
        $conf['hidepages'] = 'hidden:.*';

        saveWikiText('hidden:page', 'content', 'init');
        saveWikiText('visible:page', 'content', 'init');

        $pages = ['hidden:page' => true, 'visible:page' => true];

        // default: hidden pages are filtered
        $result = MetadataSearch::filterPages($pages);
        $this->assertArrayNotHasKey('hidden:page', $result);
        $this->assertArrayHasKey('visible:page', $result);

        // ignorePerms: hidden pages are kept
        $result = MetadataSearch::filterPages($pages, true);
        $this->assertArrayHasKey('hidden:page', $result);
        $this->assertArrayHasKey('visible:page', $result);
    }

    /**
     * filterPages respects ACL permissions
     */
    public function testFilterPagesACL()
    {
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'admin';
        $conf['useacl'] = 1;

        $AUTH_ACL = [
            '*           @ALL           8',
            'secret:*    @ALL           0',
        ];

        $_SERVER['REMOTE_USER'] = 'user';

        saveWikiText('public:page', 'content', 'init');
        saveWikiText('secret:page', 'content', 'init');

        $pages = ['public:page' => true, 'secret:page' => true];

        $result = MetadataSearch::filterPages($pages);
        $this->assertArrayHasKey('public:page', $result);
        $this->assertArrayNotHasKey('secret:page', $result);

        $result = MetadataSearch::filterPages($pages, true);
        $this->assertArrayHasKey('public:page', $result);
        $this->assertArrayHasKey('secret:page', $result);
    }

    /**
     * filterPages filters by modification time
     */
    public function testFilterPagesTime()
    {
        saveWikiText('wiki:timepage', 'content', 'init');
        $mtime = filemtime(wikiFN('wiki:timepage'));

        $pages = ['wiki:timepage' => true];

        // after: page mtime is before the threshold → filtered
        $result = MetadataSearch::filterPages($pages, false, $mtime + 100);
        $this->assertEmpty($result);

        // after: page mtime is after the threshold → kept
        $result = MetadataSearch::filterPages($pages, false, $mtime - 100);
        $this->assertArrayHasKey('wiki:timepage', $result);

        // before: page mtime is after the threshold → filtered
        $result = MetadataSearch::filterPages($pages, false, null, $mtime - 100);
        $this->assertEmpty($result);

        // before: page mtime is before the threshold → kept
        $result = MetadataSearch::filterPages($pages, false, null, $mtime + 100);
        $this->assertArrayHasKey('wiki:timepage', $result);
    }

    /**
     * filterPages preserves original array values
     */
    public function testFilterPagesPreservesValues()
    {
        saveWikiText('wiki:testpage', 'content', 'init');

        $pages = ['wiki:testpage' => 'My Title'];
        $result = MetadataSearch::filterPages($pages);

        $this->assertEquals('My Title', $result['wiki:testpage']);
    }
}
