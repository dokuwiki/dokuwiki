<?php

namespace dokuwiki\test\Ui;

use dokuwiki\Ui\PageDiff;

/**
 * Tests for the page diff view dokuwiki\Ui\PageDiff.
 */
class PageDiffTest extends \DokuWikiTest
{
    /**
     * Determine which two revisions PageDiff would compare for a ?do=diff request
     * that carries no rev or rev2 parameters.
     *
     * Clears any leftover request parameters, then runs PageDiff's internal request
     * handler, which derives the default comparison pair from the changelog. Returns
     * the timestamps it selected for the older and newer side of the diff.
     *
     * @param string $page page id to diff
     * @return array the older (rev1) and newer (rev2) revision timestamps or false
     */
    private function resolveDefaultDiff($page)
    {
        global $INPUT, $INFO;
        // make sure no rev parameters leak in from a previous request
        $INPUT->remove('rev');
        $INPUT->remove('rev2');
        $INFO['id'] = $page;

        $diff = new PageDiff($page);
        $this->callInaccessibleMethod($diff, 'handle', []);
        return [
            $this->getInaccessibleProperty($diff, 'rev1'),
            $this->getInaccessibleProperty($diff, 'rev2'),
        ];
    }

    /**
     * Without rev parameters, the diff of an existing page compares the previous
     * revision with the current one.
     */
    public function testExistingPageComparesPreviousToCurrent()
    {
        $page = 'pagediff_existing';
        // the page file's mtime after each save is that revision's timestamp
        saveWikiText($page, 'first content', 'create', false);
        clearstatcache();
        $previous = filemtime(wikiFN($page));
        $this->waitForTick(true);
        saveWikiText($page, 'second content', 'edit', false);
        clearstatcache();
        $current = filemtime(wikiFN($page));

        [$rev1, $rev2] = $this->resolveDefaultDiff($page);

        $this->assertEquals($previous, $rev1, 'older side should be the previous revision');
        $this->assertEquals($current, $rev2, 'newer side should be the current revision');
    }

    /**
     * Regression test for issue #4635: opening the default diff of a page deleted
     * through DokuWiki compares the last edit with the deletion, instead of comparing
     * the deletion entry with itself.
     */
    public function testNormalDeletionComparesPreviousToDeletion()
    {
        $page = 'pagediff_deleted';
        saveWikiText($page, 'some content', 'create', false);
        clearstatcache();
        $contentRev = filemtime(wikiFN($page)); // last revision that still had content
        $this->waitForTick(true);

        saveWikiText($page, '', 'delete', false);
        clearstatcache();
        $this->assertFileDoesNotExist(wikiFN($page));

        [$rev1, $rev2] = $this->resolveDefaultDiff($page);

        $this->assertEquals($contentRev, $rev1, 'older side should be the content revision before deletion');
        $this->assertGreaterThan(
            $rev1,
            $rev2,
            'newer side must be the later deletion, not the same revision compared with itself'
        );
    }

    /**
     * Regression test for issue #4635: opening the default diff of an externally
     * deleted page compares the last edit with the persisted deletion, instead of
     * comparing the deletion entry with itself.
     */
    public function testExternalDeletionComparesPreviousToDeletion()
    {
        $page = 'pagediff_extdeleted';
        saveWikiText($page, 'some content', 'create', false);
        clearstatcache();
        $contentRev = filemtime(wikiFN($page)); // last revision that still had content

        // delete the page file externally, bypassing DokuWiki; resolving the diff is
        // what triggers detection and persistence of the synthesized deletion entry,
        // which is dated lastRev+1 at the earliest, so no tick is needed here
        unlink(wikiFN($page));
        clearstatcache();

        [$rev1, $rev2] = $this->resolveDefaultDiff($page);

        $this->assertEquals($contentRev, $rev1, 'older side should be the content revision before external deletion');
        $this->assertGreaterThan(
            $rev1,
            $rev2,
            'newer side must be the later deletion, not the same revision compared with itself'
        );
    }

    /**
     * Regression test for issue #4635: the rendered diff of a deleted page shows the
     * removed content rather than an empty diff.
     */
    public function testDeletionDiffRendersRemovedContent()
    {
        global $INPUT, $INFO;

        $page = 'pagediff_removedcontent';
        saveWikiText($page, 'zqxdistinctivebody', 'create', false);
        $this->waitForTick(true);
        saveWikiText($page, '', 'delete', false);
        clearstatcache();

        $INPUT->remove('rev');
        $INPUT->remove('rev2');
        $INFO['id'] = $page;

        $diff = new PageDiff($page);
        ob_start();
        $diff->show();
        $html = ob_get_clean();

        $this->assertStringContainsString(
            'zqxdistinctivebody',
            $html,
            'the diff should display the removed content, not an empty diff'
        );
    }
}
