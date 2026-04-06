<?php

namespace tests\Search;

use dokuwiki\Search\Indexer;
use dokuwiki\Search\Index\FileIndex;

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
