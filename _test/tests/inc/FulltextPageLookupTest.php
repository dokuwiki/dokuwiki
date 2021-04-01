<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Test cases search only in a namespace or exclude a namespace
 */
class FulltextPageLookupTest extends DokuWikiTest {

    public function test_inoutns() {
        saveWikiText('test:page1', 'Some text', 'Test initialization');
        idx_addPage('test:page1');
        saveWikiText('ns:page2', 'Other text', 'Test initialization');
        idx_addPage('ns:page2');

        $this->assertEquals(['test:page1' => null, 'ns:page2' => null], ft_pageLookup('page'));
        $this->assertEquals(['test:page1' => null], ft_pageLookup('page @test'));
        $this->assertEquals(['ns:page2' => null], ft_pageLookup('page ^test'));
    }

}
