<?php

use dokuwiki\Search\MetadataIndex;
use dokuwiki\Search\PageIndex;
use dokuwiki\Search\PagewordIndex;

/**
 * Test cases for the PageIndex::renamePage and MetadataIndex::renameMetaValue methods
 */
class indexer_rename_test extends DokuWikiTest
{
    private $old_id = 'old_testid';

    public function setUp()
    {
        parent::setUp();
        $PageIndex = PageIndex::getInstance();
        $PageIndex->clear();

        saveWikiText($this->old_id, 'Old test content', 'Created old test page for indexer rename test');
        $PageIndex->addPage($this->old_id);
    }

    public function test_rename_to_new_page()
    {
        $PageIndex = PageIndex::getInstance();
        $PagewordIndex = PagewordIndex::getInstance();

        $newid = 'new_id_1';

        $oldpid = $PageIndex->getPID($this->old_id);

        $this->assertTrue($PageIndex->renamePage($this->old_id, $newid), 'Renaming the page to a new id failed');
        io_rename(wikiFN($this->old_id), wikiFN($newid));

        $this->assertNotEquals($PageIndex->getPID($this->old_id), $oldpid, 'PID for the old page unchanged after rename.');
        $this->assertEquals($PageIndex->getPID($newid), $oldpid, 'New page has not the old pid.');
        $query = array('old');
        $this->assertEquals(array('old' => array($newid => 1)), $PagewordIndex->lookup($query), '"Old" doesn\'t find the new page');
    }

    public function test_rename_to_existing_page()
    {
        $PageIndex = PageIndex::getInstance();
        $PagewordIndex = PagewordIndex::getInstance();

        $newid = 'existing_page';
        saveWikiText($newid, 'Existing content', 'Created page for move_to_existing_page');
        ->addPage($newid);

        $oldpid = $PageIndex->getPID($this->old_id);
        $existingpid = $PageIndex->getPID($newid);

        $this->assertTrue($PageIndex->renamePage($this->old_id, $newid), 'Renaming the page to an existing id failed');

        $this->assertNotEquals($PageIndex->getPID($this->old_id), $oldpid, 'PID for old page unchanged after rename.');
        $this->assertNotEquals($PageIndex->getPID($this->old_id), $existingpid, 'PID for old page is now PID of the existing page.');
        $this->assertEquals($PageIndex->getPID($newid), $oldpid, 'New page has not the old pid.');
        $query = array('existing');
        $this->assertEquals(array('existing' => array()), $PagewordIndex->lookup($query), 'Existing page hasn\'t been deleted from the index.');
        $query = array('old');
        $this->assertEquals(array('old' => array($newid => 1)), $PagewordIndex->lookup($query), '"Old" doesn\'t find the new page');
    }

    public function test_meta_rename_to_new_value()
    {
        $MetadataIndex = MetadataIndex::getInstance();

        $MetadataIndex->addMetaKeys($this->old_id, array('mkey' => 'old_value'));

        $this->assertTrue($MetadataIndex->renameMetaValue('mkey', 'old_value', 'new_value'), 'Meta value rename to new value failed.');
        $query = 'old_value';
        $this->assertEquals(array(), $MetadataIndex->lookupKey('mkey', $query), 'Page can still be found under old value.');
        $query = 'new_value';
        $this->assertEquals(array($this->old_id), $MetadataIndex->lookupKey('mkey', $query), 'Page can\'t be found under new value.');
    }

    public function test_meta_rename_to_existing_value()
    {
        $PageIndex = PageIndex::getInstance();
        $MetadataIndex = MetadataIndex::getInstance();

        $MetadataIndex->addMetaKeys($this->old_id, array('mkey' => array('old_value', 'new_value')));

        saveWikiText('newvalue', 'Test page', '');
        $PageIndex->addPage('newvalue');
        $MetadataIndex->addMetaKeys('newvalue', array('mkey' => array('new_value')));

        saveWikiText('oldvalue', 'Test page', '');
        $PageIndex->addPage('oldvalue');
        $MetadataIndex->addMetaKeys('oldvalue', array('mkey' => array('old_value')));

        $this->assertTrue($MetadataIndex->renameMetaValue('mkey', 'old_value', 'new_value'), 'Meta value rename to existing value failed');
        $query = 'old_value';
        $this->assertEquals(array(), $MetadataIndex->lookupKey('mkey', $query), 'Page can still be found under old value.');
        $query = 'new_value';
        $result = $MetadataIndex->lookupKey('mkey', $query);
        $this->assertContains($this->old_id, $result, 'Page with both values can\'t be found anymore');
        $this->assertContains('newvalue', $result, 'Page with new value can\'t be found anymore');
        $this->assertContains('oldvalue', $result, 'Page with only the old value can\'t be found anymore');
    }
}
