<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases for the link index
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class fulltext_backlinks_test extends DokuWikiTest {

    public function test_internallink() {
        saveWikiText('test:internallinks', '[[internälLink]] [[..:internal link]]', 'Test initialization');
        idx_addPage('test:internallinks');

        $this->assertEquals(array('test:internallinks'), ft_backlinks('internal_link'));
        $this->assertEquals(array('test:internallinks'), ft_backlinks('test:internaellink'));
    }

    public function test_links_in_footnotes() {
        saveWikiText('test:link_footnotes', '(([[footnote]] [[:foÖtnotel]]))', 'Test initialization');
        idx_addPage('test:link_footnotes');

        $this->assertEquals(array('test:link_footnotes'), ft_backlinks('test:footnote'));
        $this->assertEquals(array('test:link_footnotes'), ft_backlinks('fooetnotel'));
    }

    public function test_links_in_hidden_pages() {
        global $conf;
        $conf['hidepages'] = 'hidden:.*';
        saveWikiText('hidden:links', '[[wiki:hiddenlink|linktitle]]', 'Test initialization');
        idx_addPage('hidden:links');
        saveWikiText('visible:links', '[[wiki:hiddenlink]]', 'Test initialization');
        idx_addPage('visible:links');

        $this->assertEquals(array('visible:links'), ft_backlinks('wiki:hiddenlink'));
        $this->assertEquals(array('visible:links'), ft_backlinks('wiki:hiddenlink', false));
        $this->assertEquals(array('hidden:links', 'visible:links'), ft_backlinks('wiki:hiddenlink', true));
    }

    public function test_links_in_protected_pages() {
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
        idx_addPage('secret:links');
        saveWikiText('public:links', '[[wiki:secretlink]]', 'Test initialization');
        idx_addPage('public:links');

        $this->assertEquals(array('public:links'), ft_backlinks('wiki:secretlink'));
        $this->assertEquals(array('public:links'), ft_backlinks('wiki:secretlink', false));
        $this->assertEquals(array('public:links', 'secret:links'), ft_backlinks('wiki:secretlink', true));
    }

    public function test_links_in_deleted_pages() {
        saveWikiText('test:internallinks', '[[internallink]] [[..:internal link]]', 'Test initialization');
        idx_addPage('test:internallinks');

        $this->assertEquals(array('test:internallinks'), ft_backlinks('test:internallink'));
        $this->assertEquals(array('test:internallinks'), ft_backlinks('internal_link'));

        saveWikiText('test:internallinks', '', 'Deleted');

        $this->assertEquals(array(), ft_backlinks('test:internallink'));
        $this->assertEquals(array(), ft_backlinks('internal_link'));
    }

    function test_parameters() {
        saveWikiText('test:links', '[[wiki:syntax?do=export_raw]] [[:web:scripts:add_vhost.sh?do=export_raw]]', 'Init tests');
        idx_addPage('test:links');

        $this->assertEquals(array('test:links'), ft_backlinks('wiki:syntax'));
        $this->assertEquals(array('test:links'), ft_backlinks('web:scripts:add_vhost.sh'));
    }
}
