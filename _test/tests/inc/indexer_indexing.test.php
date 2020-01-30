<?php

use dokuwiki\Search\Indexer;
use dokuwiki\Search\PagewordIndex;
use dokuwiki\Search\MetadataIndex;

/**
 * Tests the indexing functionality of the indexer
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class indexer_indexing_test extends DokuWikiTest
{
    public function setUp()
    {
        parent::setUp();
        saveWikiText('testpage', 'Foo bar baz.', 'Test initialization');
        saveWikiText('notfound', 'Foon barn bazn.', 'Test initialization');
        $Indexer = Indexer::getInstance();
        $Indexer->addPage('testpage');
        $Indexer->addPage('notfound');
    }

    public function test_words()
    {
        $PagewordIndex = PagewordIndex::getInstance();
        $query = array('baz', 'foo');
        $this->assertEquals(array('baz' => array('testpage' => 1), 'foo' => array('testpage' => 1)), $PagewordIndex->lookup($query));
    }

    public function test_numerically_identical_words()
    {
        $PagewordIndex = PagewordIndex::getInstance();
        $PagewordIndex->addPageWords('testpage', '0x1 002');
        $PagewordIndex->addPageWords('notfound', '0x2');
        $query = array('001', '002');
        $this->assertEquals(array('001' => array(), '002' => array('testpage' => 1)), $PagewordIndex->lookup($query));
    }

    public function test_meta()
    {
        $MetadataIndex = MetadataIndex::getInstance();
        $MetadataIndex->addMetaKeys('testpage', 'testkey', 'testvalue');
        $MetadataIndex->addMetaKeys('notfound', 'testkey', 'notvalue');
        $query = 'testvalue';
        $this->assertEquals(array('testpage'), $MetadataIndex->lookupKey('testkey', $query));
    }

    public function test_numerically_identical_meta_values()
    {
        $MetadataIndex = MetadataIndex::getInstance();
        $MetadataIndex->addMetaKeys('testpage', 'numkey', array('0001', '01'));
        $MetadataIndex->addMetaKeys('notfound', 'numkey', array('00001', '000001'));
        $query = array('001', '01');
        $this->assertEquals(array('001' => array(), '01' => array('testpage')), $MetadataIndex->lookupKey('numkey', $query));
    }

    public function test_numeric_twice()
    {
        $PagewordIndex = PagewordIndex::getInstance();
        $PagewordIndex->addPageWords('testpage', '| 1010 | Dallas |');
        $query = array('1010');
        $this->assertEquals(array('1010' => array('testpage' => 1)), $PagewordIndex->lookup($query));
        $PagewordIndex->addPageWords('notfound', '| 1010 | Dallas |');
        $this->assertEquals(array('1010' => array('testpage' => 1, 'notfound' => 1)), $PagewordIndex->lookup($query));
    }

    public function test_numeric_twice_meta()
    {
        $MetadataIndex = MetadataIndex::getInstance();
        $MetadataIndex->addMetaKeys('testpage', 'onezero', array('1010'));
        $MetadataIndex->addMetaKeys('notfound', 'onezero', array('1010'));
        $query = '1010';
        $this->assertEquals(array('notfound', 'testpage'), $MetadataIndex->lookupKey('onezero', $query));
    }

    public function test_numeric_zerostring_meta()
    {
        $MetadataIndex = MetadataIndex::getInstance();
        $MetadataIndex->addMetaKeys('zero1', 'zerostring', array('0'));
        $MetadataIndex->addMetaKeys('zero2', 'zerostring', array('0'));
        $MetadataIndex->addMetaKeys('0', 'zerostring', array('zero'));

        $query = '0';
        $result = $MetadataIndex->lookupKey('zerostring', $query);
        sort($result);
        $this->assertEquals(array('zero1', 'zero2'), $result);

        $query = 'zero';
        $result = $MetadataIndex->lookupKey('zerostring', $query);
        sort($result);
        $this->assertEquals(array('0'), $result);
    }
}
