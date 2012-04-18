<?php

/**
 * @group integration
 */
class InttestsPHPQueryTest extends DokuWikiTest {
    /**
     * Execute the simplest possible request and check the
     * meta generator tag is set to "DokuWiki"
     */
    function testSimpleRun() {
        $request = new TestRequest();

        $response = $request->execute();

        $this->assertEquals('DokuWiki', $response->queryHTML('meta[name="generator"]')->attr('content') );
    }
}
