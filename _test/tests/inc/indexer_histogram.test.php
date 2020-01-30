<?php

use dokuwiki\Search\Indexer;
use dokuwiki\Search\MetadataIndex;

/**
 * Tests the histogram function of the indexer.
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class indexer_histogram_test extends DokuWikiTest
{
    public function test_minlength()
    {
        $MetadataIndex = MetadataIndex::getInstance();
        $MetadataIndex->addMetaKeys('histo1', 'testkey', array('foo', 'bar', 'foobar'));
        $MetadataIndex->addMetaKeys('histo2', 'testkey', array('bar', 'testing'));
        $MetadataIndex->addMetaKeys('histo3', 'testkey', array('foo', 'foobar'));

        $Indexer = Indexer::getInstance();
        $histogram4 = $Indexer->histogram(1, 0, 4, 'testkey');
        $this->assertEquals(array('foobar' => 2, 'testing' => 1), $histogram4);
        $histogram2 = $Indexer->histogram(1, 0, 2, 'testkey');
        $this->assertEquals(array('foobar' => 2, 'testing' => 1, 'foo' => 2, 'bar' => 2), $histogram2);
    }
}
