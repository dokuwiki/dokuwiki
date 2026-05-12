<?php

namespace dokuwiki\test\Search;

use dokuwiki\Search\Indexer;
use dokuwiki\Search\MetadataSearch;

/**
 * Test cases for the media usage search
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class MediauseTest extends \DokuWikiTest
{
    public function testInternalmedia()
    {
        saveWikiText('test:internalmedia_usage', '{{internalmedia.png}} {{..:internal media.png}}', 'Test initialization');
        (new Indexer())->addPage('test:internalmedia_usage');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:internalmedia_usage'), $search->mediause('internal_media.png'));
        $this->assertEquals(array('test:internalmedia_usage'), $search->mediause('test:internalmedia.png'));
    }

    public function testMediaInLinks()
    {
        saveWikiText('test:medialinks', '[[doku>wiki:dokuwiki|{{wiki:logo.png}}]] [[http://www.example.com|{{example.png?200x800}}]]', 'Test init');
        (new Indexer())->addPage('test:medialinks');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:medialinks'), $search->mediause('wiki:logo.png'));
        $this->assertEquals(array('test:medialinks'), $search->mediause('test:example.png'));
    }

    public function testMediaInLocalLinks()
    {
        saveWikiText('test:locallinks', '[[#test|{{wiki:logolocal.png}}]]', 'Test init');
        (new Indexer())->addPage('test:locallinks');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:locallinks'), $search->mediause('wiki:logolocal.png'));
    }

    public function testMediaInFootnotes()
    {
        saveWikiText('test:media_footnotes', '(({{footnote.png?20x50}} [[foonote|{{:footlink.png}}]]))', 'Test initialization');
        (new Indexer())->addPage('test:media_footnotes');
        $search = new MetadataSearch();

        $this->assertEquals(array('test:media_footnotes'), $search->mediause('test:footnote.png'));
        $this->assertEquals(array('test:media_footnotes'), $search->mediause('footlink.png'));
    }

    public function testMediaInHiddenPages()
    {
        global $conf;
        $conf['hidepages'] = 'hidden:.*';
        saveWikiText('hidden:medias', '[[doku>wiki:dokuwiki|{{wiki:hiddenlogo.png}}]]', 'Test initialization');
        (new Indexer())->addPage('hidden:medias');
        $search = new MetadataSearch();

        $this->assertEquals(array(), $search->mediause('wiki:hiddenlogo.png'));
        $this->assertEquals(array(), $search->mediause('wiki:hiddenlogo.png', false));
        $this->assertEquals(array('hidden:medias'), $search->mediause('wiki:hiddenlogo.png', true));
    }

    public function testMediaInProtectedPages()
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
        (new Indexer())->addPage('secret:medias');
        $search = new MetadataSearch();

        $this->assertEquals(array(), $search->mediause('wiki:secretlogo.png'));
        $this->assertEquals(array(), $search->mediause('wiki:secretlogo.png', false));
        $this->assertEquals(array('secret:medias'), $search->mediause('wiki:secretlogo.png', true));
    }

    public function testMediaInDeletedPages()
    {
        saveWikiText('test:internalmedia_usage', '{{internalmedia.png}} {{..:internal media.png}}', 'Test initialization');
        (new Indexer())->addPage('test:internalmedia_usage');
        saveWikiText('test:internalmedia_usage', '', 'Deleted');
        $search = new MetadataSearch();

        $this->assertEquals(array(), $search->mediause('internal_media.png'));
        $this->assertEquals(array(), $search->mediause('test:internalmedia.png'));
    }
}
