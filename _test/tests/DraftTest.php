<?php

namespace dokuwiki\test;

use dokuwiki\Draft;

/**
 * Tests for the draft handling of edit sessions
 *
 * @group draft
 */
class DraftTest extends \DokuWikiTest
{
    /**
     * Stage the edit-session POST fields the way the editor would send them.
     *
     * @param string $wikitext the edited text
     * @param string $prefix the section prefix (with its trailing boundary newline)
     * @param string $suffix the section suffix
     * @param int $date the revision date of the edited page
     */
    protected function setEditPost(string $wikitext, string $prefix = '', string $suffix = '', int $date = 0): void
    {
        global $INPUT;
        $INPUT->post->set('wikitext', $wikitext);
        $INPUT->post->set('prefix', $prefix);
        $INPUT->post->set('suffix', $suffix);
        $INPUT->post->set('date', $date);
    }

    /**
     * A whole-page edit is saved and read back unchanged.
     */
    public function testSaveAndRetrieveWholePage(): void
    {
        global $conf;
        $conf['usedraft'] = 1;

        $this->setEditPost('Hello world');
        $draft = new Draft('draft:wholepage', 'tester');

        $this->assertTrue($draft->saveDraft());
        $this->assertTrue($draft->isDraftAvailable());
        $this->assertFileExists($draft->getDraftFilename());
        $this->assertSame('Hello world', $draft->getDraftText());
    }

    /**
     * A section edit is reassembled from prefix, text and suffix when read back. The editor
     * posts the prefix with a trailing boundary newline that is stripped on save and
     * re-inserted on retrieval.
     */
    public function testSaveReassemblesSectionEdit(): void
    {
        global $conf;
        $conf['usedraft'] = 1;

        $this->setEditPost('BODY', "PRE\n", 'SUF');
        $draft = new Draft('draft:section', 'tester');
        $this->assertTrue($draft->saveDraft());

        $this->assertSame("PRE\nBODY\nSUF", $draft->getDraftText());
    }

    /**
     * No draft is written when drafts are disabled in the configuration.
     */
    public function testNotSavedWhenDisabled(): void
    {
        global $conf;
        $conf['usedraft'] = 0;

        $this->setEditPost('Hello world');
        $draft = new Draft('draft:disabled', 'tester');

        $this->assertFalse($draft->saveDraft());
        $this->assertFalse($draft->isDraftAvailable());
    }

    /**
     * No draft is written when nothing was edited and no handler wants the event.
     */
    public function testNotSavedWithoutText(): void
    {
        global $conf;
        $conf['usedraft'] = 1;

        $draft = new Draft('draft:notext', 'tester');

        $this->assertFalse($draft->saveDraft());
        $this->assertFalse($draft->isDraftAvailable());
    }

    /**
     * Deleting a draft removes it from disk.
     */
    public function testDeleteRemovesTheDraft(): void
    {
        global $conf;
        $conf['usedraft'] = 1;

        $this->setEditPost('Hello world');
        $draft = new Draft('draft:delete', 'tester');
        $draft->saveDraft();
        $this->assertTrue($draft->isDraftAvailable());

        $draft->deleteDraft();
        $this->assertFalse($draft->isDraftAvailable());
        $this->assertFileDoesNotExist($draft->getDraftFilename());
    }

    /**
     * Reading a draft that does not exist is an error.
     */
    public function testGetDraftTextThrowsWhenMissing(): void
    {
        $draft = new Draft('draft:missing', 'tester');
        $this->assertFalse($draft->isDraftAvailable());

        $this->expectException(\RuntimeException::class);
        $draft->getDraftText();
    }

    /**
     * A draft older than the current page revision is stale and gets purged on construction,
     * so a user is never offered a draft that predates the saved content.
     */
    public function testStaleDraftPurgedOnConstruction(): void
    {
        global $conf;
        $conf['usedraft'] = 1;

        $id = 'draft:stale';
        $this->setEditPost('draft body');
        $draft = new Draft($id, 'tester');
        $draft->saveDraft();
        $file = $draft->getDraftFilename();
        $this->assertFileExists($file);

        // a newer page revision now exists
        io_saveFile(wikiFN($id), 'saved by someone else');
        touch(wikiFN($id), filemtime($file) + 10);

        // re-opening the draft must purge it as stale
        $fresh = new Draft($id, 'tester');
        $this->assertFalse($fresh->isDraftAvailable());
        $this->assertFileDoesNotExist($file);
    }

    /**
     * A draft newer than the current page revision is kept on construction.
     */
    public function testFreshDraftKeptOnConstruction(): void
    {
        global $conf;
        $conf['usedraft'] = 1;

        $id = 'draft:fresh';
        // an older page revision exists
        io_saveFile(wikiFN($id), 'older content');

        $this->setEditPost('newer draft');
        $draft = new Draft($id, 'tester');
        $draft->saveDraft();
        $file = $draft->getDraftFilename();
        touch($file, filemtime(wikiFN($id)) + 10);

        $fresh = new Draft($id, 'tester');
        $this->assertTrue($fresh->isDraftAvailable());
    }
}
