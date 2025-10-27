<?php


/**
 * Test cases search only in a namespace or exclude a namespace
 */
class FulltextPageLookupTest extends DokuWikiTest {

    public function test_inoutns() {
        saveWikiText('test:page1', 'Some text', 'Test initialization');
        idx_addPage('test:page1');
        saveWikiText('ns:page2', 'Other text', 'Test initialization');
        idx_addPage('ns:page2');
        saveWikiText('ns:utf8', '====== Title with ÄöÜ ======', 'Test initialization');
        idx_addPage('ns:utf8');

        $this->assertEquals(['test:page1' => null, 'ns:page2' => null], ft_pageLookup('page'));
        $this->assertEquals(['test:page1' => null], ft_pageLookup('page @test'));
        $this->assertEquals(['ns:page2' => null], ft_pageLookup('page ^test'));

        $this->assertEquals(['ns:utf8' => 'Title with ÄöÜ'], ft_pageLookup('title', false, true));
        $this->assertEquals(['ns:utf8' => 'Title with ÄöÜ'], ft_pageLookup('äöü', false, true));
    }

}
