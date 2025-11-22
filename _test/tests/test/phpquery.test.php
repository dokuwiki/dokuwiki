<?php

/**
 * @group integration
 */
class InttestsPHPQueryTest extends EasyWikiTest {
    /**
     * Execute the simplest possible request and check the
     * meta generator tag is set to "EasyWiki"
     */
    function testSimpleRun() {
        $request = new TestRequest();

        $response = $request->execute();

        $this->assertEquals('EasyWiki', $response->queryHTML('meta[name="generator"]')->attr('content') );
    }
}
