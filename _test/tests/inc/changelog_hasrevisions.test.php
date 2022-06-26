<?php

/**
 * Tests for if a page has revisions with hasRevisions()
 *
 * This class uses the files:
 * - data/meta/mailinglist.changes
 */
class changelog_hasrevisions_test extends DokuWikiTest {

    /**
     * test page has revisions
     */
    function test_hasrevisions() {
        $id = 'mailinglist';
        
        $pagelog = new PageChangeLog($id);
        $result = $pagelog->hasRevisions();
        $this->assertTrue($result);
    }
    
    /**
     * test page has no revisions
     */
    function test_norevisions() {
        $id = 'nonexist';
        
        $pagelog = new PageChangeLog($id);
        $result = $pagelog->hasRevisions();
        $this->assertFalse($result);
    }
}
