<?php

use dokuwiki\Search\Indexer;
use dokuwiki\Search\MetadataIndex;

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for the link index
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class fulltext_backlinks_test extends DokuWikiTest
{
    public function test_internallink()
    {
        saveWikiText('test:internallinks', '[[internälLink]] [[..:internal link]]', 'Test initialization');
        $Indexer = Indexer::getInstance();
        $Indexer->addPage('test:internallinks');
        $MetadataIndex = MetadataIndex::getInstance();

        $this->assertEquals(array('test:internallinks'), $MetadataIndex->backlinks('internal_link'));
        $this->assertEquals(array('test:internallinks'), $MetadataIndex->backlinks('test:internaellink'));
    }

    public function test_links_in_footnotes()
    {
        saveWikiText('test:link_footnotes', '(([[footnote]] [[:foÖtnotel]]))', 'Test initialization');
        $Indexer = Indexer::getInstance();
        $Indexer->addPage('test:link_footnotes');
        $MetadataIndex = MetadataIndex::getInstance();

        $this->assertEquals(array('test:link_footnotes'), $MetadataIndex->backlinks('test:footnote'));
        $this->assertEquals(array('test:link_footnotes'), $MetadataIndex->backlinks('fooetnotel'));
    }

    public function test_links_in_hidden_pages()
    {
        global $conf;
        $conf['hidepages'] = 'hidden:.*';
        $Indexer = Indexer::getInstance();
        saveWikiText('hidden:links', '[[wiki:hiddenlink|linktitle]]', 'Test initialization');
        $Indexer->addPage('hidden:links');
        saveWikiText('visible:links', '[[wiki:hiddenlink]]', 'Test initialization');
        $Indexer->addPage('visible:links');
        $MetadataIndex = MetadataIndex::getInstance();

        $this->assertEquals(array('visible:links'), $MetadataIndex->backlinks('wiki:hiddenlink'));
        $this->assertEquals(array('visible:links'), $MetadataIndex->backlinks('wiki:hiddenlink', false));
        $this->assertEquals(array('hidden:links', 'visible:links'), $MetadataIndex->backlinks('wiki:hiddenlink', true));
    }

    public function test_links_in_protected_pages()
    {
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'alice';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*           @ALL           8',
            'secret:*      @ALL           0',
        );

        $_SERVER['REMOTE_USER'] = 'eve';

        $Indexer = Indexer::getInstance();
        saveWikiText('secret:links', '[[wiki:secretlink]]', 'Test initialization');
        $Indexer->addPage('secret:links');
        saveWikiText('public:links', '[[wiki:secretlink]]', 'Test initialization');
        $Indexer->addPage('public:links');
        $MetadataIndex = MetadataIndex::getInstance();

        $this->assertEquals(array('public:links'), $MetadataIndex->backlinks('wiki:secretlink'));
        $this->assertEquals(array('public:links'), $MetadataIndex->backlinks('wiki:secretlink', false));
        $this->assertEquals(array('public:links', 'secret:links'), $MetadataIndex->backlinks('wiki:secretlink', true));
    }

    public function test_links_in_deleted_pages()
    {
        $Indexer = Indexer::getInstance();
        saveWikiText('test:internallinks', '[[internallink]] [[..:internal link]]', 'Test initialization');
        $Indexer->addPage('test:internallinks');
        $MetadataIndex = MetadataIndex::getInstance();

        $this->assertEquals(array('test:internallinks'), $MetadataIndex->backlinks('test:internallink'));
        $this->assertEquals(array('test:internallinks'), $MetadataIndex->backlinks('internal_link'));

        saveWikiText('test:internallinks', '', 'Deleted');

        $this->assertEquals(array(), $MetadataIndex->backlinks('test:internallink'));
        $this->assertEquals(array(), $MetadataIndex->backlinks('internal_link'));
    }

    function test_parameters()
    {
        $Indexer = Indexer::getInstance();
        saveWikiText('test:links', '[[wiki:syntax?do=export_raw]] [[:web:scripts:add_vhost.sh?do=export_raw]]', 'Init tests');
        $Indexer->addPage('test:links');
        $MetadataIndex = MetadataIndex::getInstance();

        $this->assertEquals(array('test:links'), $MetadataIndex->backlinks('wiki:syntax'));
        $this->assertEquals(array('test:links'), $MetadataIndex->backlinks('web:scripts:add_vhost.sh'));
    }
}
