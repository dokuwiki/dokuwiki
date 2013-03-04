<?php
/**
 * Tests the histogram function of the indexer.
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class indexer_histogram_test extends DokuWikiTest {
    function test_minlength() {
        $indexer = idx_get_indexer();
        $indexer->addMetaKeys('histo1', 'testkey', array('foo', 'bar', 'foobar'));
        $indexer->addMetaKeys('histo2', 'testkey', array('bar', 'testing'));
        $indexer->addMetaKeys('histo3', 'testkey', array('foo', 'foobar'));
        $histogram4 = $indexer->histogram(1, 0, 4, 'testkey');
        $this->assertEquals(array('foobar' => 2, 'testing' => 1), $histogram4);
        $histogram2 = $indexer->histogram(1, 0, 2, 'testkey');
        $this->assertEquals(array('foobar' => 2, 'testing' => 1, 'foo' => 2, 'bar' => 2), $histogram2);
    }

}
