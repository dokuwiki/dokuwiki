<?php

namespace dokuwiki\test\Search;

use dokuwiki\Search\Indexer;
use dokuwiki\Search\Index\FileIndex;
use dokuwiki\Search\MetadataSearch;

/**
 * Tests the Indexer class
 */
class IndexerTest extends \DokuWikiTest
{
    /**
     * Test basic page indexing via addPage
     */
    public function testAddPage()
    {
        $indexer = new Indexer();

        saveWikiText('testpage', 'Foo bar baz.', 'Test initialization');
        $indexer->addPage('testpage');

        // page should be in the entity index
        $pageIndex = new FileIndex('page');
        $result = $pageIndex->search('/^testpage$/');
        $this->assertNotEmpty($result, 'testpage not found in page.idx');
    }

    /**
     * Test that deletePage clears data
     */
    public function testDeletePage()
    {
        $indexer = new Indexer();

        saveWikiText('delpage', 'Delete me content.', 'Test initialization');
        $indexer->addPage('delpage');
        $indexer->deletePage('delpage', true);

        // page entity persists in page.idx but data is cleared
        $pageIndex = new FileIndex('page');
        $result = $pageIndex->search('/^delpage$/');
        $this->assertNotEmpty($result, 'delpage should persist in page.idx');
    }

    /**
     * Test renamePage clears old and indexes new
     */
    public function testRenamePage()
    {
        $indexer = new Indexer();

        saveWikiText('old_name', 'Old page content words.', 'Test initialization');
        $indexer->addPage('old_name');

        // move the page on disk
        io_rename(wikiFN('old_name'), wikiFN('new_name'));
        saveWikiText('new_name', 'Old page content words.', 'Renamed');

        $indexer->renamePage('old_name', 'new_name');

        // new page should be indexed
        $pageIndex = new FileIndex('page');
        $result = $pageIndex->search('/^new_name$/');
        $this->assertNotEmpty($result, 'new_name not found in page.idx after rename');
    }

    /**
     * renamePage must preserve the renamed page's outgoing references
     *
     * The rename only changes the page's name in the index, not its content, so all of
     * its index associations - including the pages it links to (relation_references) -
     * must survive under the new name. This is what allows a page renamed early during a
     * namespace move to still be found as a backlink source for pages moved afterwards.
     * It must work even though the destination page is not on disk yet at rename time
     * (the move operation writes it only later), so re-indexing from disk cannot be relied
     * upon here.
     *
     * @see https://github.com/dokuwiki/dokuwiki - regression after the indexer rewrite
     */
    public function testRenamePagePreservesOutgoingReferences()
    {
        $indexer = new Indexer();

        saveWikiText('refsource', '[[target:page]]', 'Test initialization');
        $indexer->addPage('refsource');

        $search = new MetadataSearch();

        // sanity: the source page references target:page
        $value = 'target:page';
        $this->assertEquals(['refsource'], $search->lookupKey('relation_references', $value));

        // rename the source page WITHOUT writing the destination to disk first,
        // mimicking how the move plugin calls renamePage before saving the new page
        $indexer->renamePage('refsource', 'moved:newsource');

        // the outgoing reference must now belong to the renamed page
        $value = 'target:page';
        $this->assertEquals(
            ['moved:newsource'],
            $search->lookupKey('relation_references', $value),
            'rename lost the outgoing reference of the renamed page'
        );
    }

    /**
     * renamePage onto a name that already has its own index entry
     *
     * The renamed page must take over the destination name (keeping its own data) while the
     * destination's previous data is dropped. The stale destination row must be vacated so the
     * name resolves only to the renamed entity and does not leak as a phantom page.
     */
    public function testRenamePageOntoExistingPage()
    {
        $indexer = new Indexer();

        saveWikiText('src', '[[target:fromsrc]]', 'Test initialization');
        $indexer->addPage('src');
        saveWikiText('dst', '[[target:fromdst]]', 'Test initialization');
        $indexer->addPage('dst');

        $indexer->renamePage('src', 'dst');

        $search = new MetadataSearch();

        // dst now carries src's outgoing reference ...
        $value = 'target:fromsrc';
        $this->assertEquals(['dst'], $search->lookupKey('relation_references', $value));
        // ... and the destination's previous reference is gone
        $value = 'target:fromdst';
        $this->assertEquals([], $search->lookupKey('relation_references', $value));

        // exactly one entity named 'dst', the old name and any phantom entry are gone
        $allPages = $indexer->getAllPages();
        $this->assertSame(['dst'], array_values(array_filter($allPages, fn($p) => $p === 'dst' || $p === 'src')));
    }

    /**
     * Test that clear removes all index files
     */
    public function testClear()
    {
        global $conf;
        $indexer = new Indexer();

        saveWikiText('clearpage', 'Some words to index.', 'Test initialization');
        $indexer->addPage('clearpage');

        $this->assertFileExists($conf['indexdir'] . '/page.idx');

        $indexer->clear();

        $this->assertFileDoesNotExist($conf['indexdir'] . '/page.idx');
    }

    /**
     * Test that getVersion returns a version string
     */
    public function testGetVersion()
    {
        $indexer = new Indexer();
        $version = $indexer->getVersion();
        $this->assertNotEmpty($version);
        $this->assertIsString((string)$version);
    }

    /**
     * Test needsIndexing returns true for new pages
     */
    public function testNeedsIndexing()
    {
        $indexer = new Indexer();

        saveWikiText('needsidx', 'Some content.', 'Test initialization');
        $this->assertTrue($indexer->needsIndexing('needsidx'));

        $indexer->addPage('needsidx');
        // ensure the .indexed tag is strictly newer than the wiki file
        touch(metaFN('needsidx', '.indexed'), time() + 1);
        $this->assertFalse($indexer->needsIndexing('needsidx'));
        $this->assertTrue($indexer->needsIndexing('needsidx', true)); // force
    }

    /**
     * Test the logger callback
     */
    public function testLogger()
    {
        $messages = [];
        $indexer = (new Indexer())->setLogger(function ($msg) use (&$messages) {
            $messages[] = $msg;
        });

        saveWikiText('logpage', 'Log test content.', 'Test initialization');
        $indexer->addPage('logpage');

        // ensure the .indexed tag is strictly newer so second call detects "up to date"
        touch(metaFN('logpage', '.indexed'), time() + 1);

        $indexer->addPage('logpage');
        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('up to date', end($messages));
    }
}
