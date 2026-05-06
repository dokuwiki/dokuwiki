<?php

namespace dokuwiki\test\Search;

use dokuwiki\Search\Indexer;
use dokuwiki\Search\MetadataSearch;

/**
 * Test cases for the backlinks search
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class BacklinksTest extends \DokuWikiTest
{
    public function testInternallink()
    {
        saveWikiText('test:internallinks', '[[internälLink]] [[..:internal link]]', 'Test initialization');
        (new Indexer())->addPage('test:internallinks');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:internallinks'), $search->backlinks('internal_link'));
        $this->assertEquals(array('test:internallinks'), $search->backlinks('test:internaellink'));
    }

    public function testLinksInFootnotes()
    {
        saveWikiText('test:link_footnotes', '(([[footnote]] [[:foÖtnotel]]))', 'Test initialization');
        (new Indexer())->addPage('test:link_footnotes');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:link_footnotes'), $search->backlinks('test:footnote'));
        $this->assertEquals(array('test:link_footnotes'), $search->backlinks('fooetnotel'));
    }

    public function testLinksInHiddenPages()
    {
        global $conf;
        $conf['hidepages'] = 'hidden:.*';
        saveWikiText('hidden:links', '[[wiki:hiddenlink|linktitle]]', 'Test initialization');
        (new Indexer())->addPage('hidden:links');
        saveWikiText('visible:links', '[[wiki:hiddenlink]]', 'Test initialization');
        (new Indexer())->addPage('visible:links');
        $search = new MetadataSearch();

        $this->assertEquals(array('visible:links'), $search->backlinks('wiki:hiddenlink'));
        $this->assertEquals(array('visible:links'), $search->backlinks('wiki:hiddenlink', false));
        $this->assertEquals(array('hidden:links', 'visible:links'), $search->backlinks('wiki:hiddenlink', true));
    }

    public function testLinksInProtectedPages()
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

        saveWikiText('secret:links', '[[wiki:secretlink]]', 'Test initialization');
        (new Indexer())->addPage('secret:links');
        saveWikiText('public:links', '[[wiki:secretlink]]', 'Test initialization');
        (new Indexer())->addPage('public:links');
        $search = new MetadataSearch();

        $this->assertEquals(array('public:links'), $search->backlinks('wiki:secretlink'));
        $this->assertEquals(array('public:links'), $search->backlinks('wiki:secretlink', false));
        $this->assertEquals(array('public:links', 'secret:links'), $search->backlinks('wiki:secretlink', true));
    }

    public function testLinksInDeletedPages()
    {
        saveWikiText('test:internallinks', '[[internallink]] [[..:internal link]]', 'Test initialization');
        (new Indexer())->addPage('test:internallinks');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:internallinks'), $search->backlinks('test:internallink'));
        $this->assertEquals(array('test:internallinks'), $search->backlinks('internal_link'));

        saveWikiText('test:internallinks', '', 'Deleted');

        $this->assertEquals(array(), $search->backlinks('test:internallink'));
        $this->assertEquals(array(), $search->backlinks('internal_link'));
    }

    public function testParameters()
    {
        saveWikiText('test:links', '[[wiki:syntax?do=export_raw]] [[:web:scripts:add_vhost.sh?do=export_raw]]', 'Init tests');
        (new Indexer())->addPage('test:links');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:links'), $search->backlinks('wiki:syntax'));
        $this->assertEquals(array('test:links'), $search->backlinks('web:scripts:add_vhost.sh'));
    }
}
