<?php

use dokuwiki\Search\MetadataSearch;
use dokuwiki\Search\PageIndex;

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for the media usage index
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class fultext_mediause_test extends DokuWikiTest
{
    public function test_internalmedia()
    {
        saveWikiText('test:internalmedia_usage', '{{internalmedia.png}} {{..:internal media.png}}', 'Test initialization');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('test:internalmedia_usage');

        $this->assertEquals(array('test:internalmedia_usage'), MetadataSearch::mediause('internal_media.png'));
        $this->assertEquals(array('test:internalmedia_usage'), MetadataSearch::mediause('test:internalmedia.png'));
    }

    public function test_media_in_links()
    {
        saveWikiText('test:medialinks', '[[doku>wiki:dokuwiki|{{wiki:logo.png}}]] [[http://www.example.com|{{example.png?200x800}}]]', 'Test init');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('test:medialinks');

        $this->assertEquals(array('test:medialinks'), MetadataSearch::mediause('wiki:logo.png'));
        $this->assertEquals(array('test:medialinks'), MetadataSearch::mediause('test:example.png'));
    }

    public function test_media_in_local_links()
    {
        saveWikiText('test:locallinks', '[[#test|{{wiki:logolocal.png}}]]', 'Test init');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('test:locallinks');

        $this->assertEquals(array('test:locallinks'), MetadataSearch::mediause('wiki:logolocal.png'));
    }

    public function test_media_in_footnotes()
    {
        saveWikiText('test:media_footnotes', '(({{footnote.png?20x50}} [[foonote|{{:footlink.png}}]]))', 'Test initialization');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('test:media_footnotes');

        $this->assertEquals(array('test:media_footnotes'), MetadataSearch::mediause('test:footnote.png'));
        $this->assertEquals(array('test:media_footnotes'), MetadataSearch::mediause('footlink.png'));
    }

    public function test_media_in_hidden_pages()
    {
        global $conf;
        $conf['hidepages'] = 'hidden:.*';
        saveWikiText('hidden:medias', '[[doku>wiki:dokuwiki|{{wiki:hiddenlogo.png}}]]', 'Test initialization');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('hidden:medias');

        $this->assertEquals(array(), MetadataSearch::mediause('wiki:hiddenlogo.png'));
        $this->assertEquals(array(), MetadataSearch::mediause('wiki:hiddenlogo.png', false));
        $this->assertEquals(array('hidden:medias'), MetadataSearch::mediause('wiki:hiddenlogo.png', true));
    }

    public function test_media_in_protected_pages()
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

        saveWikiText('secret:medias', '[[doku>wiki:dokuwiki|{{wiki:secretlogo.png}}]]', 'Test initialization');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('secret:medias');

        $this->assertEquals(array(), MetadataSearch::mediause('wiki:secretlogo.png'));
        $this->assertEquals(array(), MetadataSearch::mediause('wiki:secretlogo.png', false));
        $this->assertEquals(array('secret:medias'), MetadataSearch::mediause('wiki:secretlogo.png', true));
    }

    public function test_media_in_deleted_pages()
    {
        saveWikiText('test:internalmedia_usage', '{{internalmedia.png}} {{..:internal media.png}}', 'Test initialization');
        $PageIndex = PageIndex::getInstance();
        $PageIndex->addPage('test:internalmedia_usage');
        saveWikiText('test:internalmedia_usage', '', 'Deleted');

        $this->assertEquals(array(), MetadataSearch::mediause('internal_media.png'));
        $this->assertEquals(array(), MetadataSearch::mediause('test:internalmedia.png'));
    }
}
