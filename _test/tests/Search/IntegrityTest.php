<?php

namespace dokuwiki\test\Search;

use dokuwiki\Search\Collection\PageFulltextCollection;
use dokuwiki\Search\Collection\PageTitleCollection;
use dokuwiki\Search\Exception\IndexIntegrityException;
use dokuwiki\Search\Indexer;

/**
 * Tests the index integrity checking
 */
class IntegrityTest extends \DokuWikiTest
{
    /**
     * Clear the index directory and indexing metadata before each test
     */
    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $files = glob($conf['indexdir'] . '/*.idx');
        foreach ($files as $file) {
            @unlink($file);
        }
        // remove the .indexed tag so needsIndexing() won't skip re-indexing
        @unlink(metaFN('integritytest', '.indexed'));
        \dokuwiki\Search\Index\Lock::releaseAll();
    }

    /**
     * Index a page so we have data to check
     */
    protected function indexTestPage(): void
    {
        saveWikiText('integritytest', 'Hello world testing integrity check.', 'Test');
        $indexer = new Indexer();
        $indexer->addPage('integritytest');
    }

    /**
     * A healthy index should not throw
     */
    public function testHealthyIndex()
    {
        $this->indexTestPage();

        $indexer = new Indexer();
        $indexer->checkIntegrity();
        $this->assertFalse($indexer->isIndexEmpty());
    }

    /**
     * An empty index should not throw
     */
    public function testEmptyIndex()
    {
        $indexer = new Indexer();
        $indexer->checkIntegrity();
        $this->assertTrue($indexer->isIndexEmpty());
    }

    /**
     * Corrupted fulltext index (token/frequency mismatch) should throw
     */
    public function testCorruptedFulltextTokenFrequency()
    {
        global $conf;
        $this->indexTestPage();

        // Append an extra line to a token index to create a mismatch
        $collection = new PageFulltextCollection();
        $max = $collection->getTokenIndexMaximum();
        $this->assertGreaterThan(0, $max);

        $tokenFile = $conf['indexdir'] . '/w' . $max . '.idx';
        $this->assertFileExists($tokenFile);
        file_put_contents($tokenFile, "corruptedentry\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new PageFulltextCollection())->checkIntegrity();
    }

    /**
     * Corrupted fulltext index (entity/reverse mismatch) should throw
     */
    public function testCorruptedFulltextEntityReverse()
    {
        global $conf;
        $this->indexTestPage();

        $reverseFile = $conf['indexdir'] . '/pageword.idx';
        $this->assertFileExists($reverseFile);
        file_put_contents($reverseFile, "0\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new PageFulltextCollection())->checkIntegrity();
    }

    /**
     * Corrupted title index (entity/token mismatch) should throw
     */
    public function testCorruptedTitleIndex()
    {
        global $conf;
        $this->indexTestPage();

        $titleFile = $conf['indexdir'] . '/title.idx';
        $this->assertFileExists($titleFile);
        file_put_contents($titleFile, "extra title\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new PageTitleCollection())->checkIntegrity();
    }

    /**
     * Indexer.checkIntegrity aggregates all collection checks
     */
    public function testIndexerCheckIntegrityDetectsCorruption()
    {
        global $conf;
        $this->indexTestPage();

        // Corrupt title index
        $titleFile = $conf['indexdir'] . '/title.idx';
        file_put_contents($titleFile, "extra title\n", FILE_APPEND);

        $this->expectException(IndexIntegrityException::class);
        (new Indexer())->checkIntegrity();
    }
}
