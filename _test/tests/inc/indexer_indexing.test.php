<?php
/**
 * Tests the indexing functionality of the indexer
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class indexer_indexing_test extends DokuWikiTest {
    public function setUp() {
        parent::setUp();
        saveWikiText('testpage', 'Foo bar baz.', 'Test initialization');
        saveWikiText('notfound', 'Foon barn bazn.', 'Test initialization');
        idx_addPage('testpage');
        idx_addPage('notfound');
    }

    public function test_words() {
        $indexer = idx_get_indexer();
        $query = array('baz', 'foo');
        $this->assertEquals(array('baz' => array('testpage' => 1), 'foo' => array('testpage' => 1)), $indexer->lookup($query));
    }

    public function test_numerically_identical_words() {
        $indexer = idx_get_indexer();
        $indexer->addPageWords('testpage', '0x1 002');
        $indexer->addPageWords('notfound', '0x2');
        $query = array('001', '002');
        $this->assertEquals(array('001' => array(), '002' => array('testpage' => 1)), $indexer->lookup($query));
    }

    public function test_meta() {
        $indexer = idx_get_indexer();
        $indexer->addMetaKeys('testpage', 'testkey', 'testvalue');
        $indexer->addMetaKeys('notfound', 'testkey', 'notvalue');
        $query = 'testvalue';
        $this->assertEquals(array('testpage'), $indexer->lookupKey('testkey', $query));
    }

    public function test_numerically_identical_meta_values() {
        $indexer = idx_get_indexer();
        $indexer->addMetaKeys('testpage', 'numkey', array('0001', '01'));
        $indexer->addMetaKeys('notfound', 'numkey', array('00001', '000001'));
        $query = array('001', '01');
        $this->assertEquals(array('001' => array(), '01' => array('testpage')), $indexer->lookupKey('numkey', $query));
    }
}