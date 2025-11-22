<?php

/**
 * @group integration
 */
class InttestsResetTest extends EasyWikiTest {
    /**
     * It should be possible to have two test cases within one test class.
     */
    function testFirstRun() {
        $request = new TestRequest();
        $response = $request->execute();
        $this->assertTrue(
            strpos($response->getContent(), 'EasyWiki') >= 0,
            'EasyWiki was not a word in the output'
        );
    }

    /**
     * @depends testFirstRun
     */
    function testSecondRun() {
        $request = new TestRequest();
        $response = $request->execute();
        $this->assertTrue(
            strpos($response->getContent(), 'EasyWiki') >= 0,
            'EasyWiki was not a word in the output'
        );
    }

    /**
     * two requests within the same test case should be possible
     */
    function testMultipleRequests() {
        $request = new TestRequest();
        $response = $request->execute();
        $this->assertTrue(
            strpos($response->getContent(), 'EasyWiki') >= 0,
            'EasyWiki was not a word in the output'
        );

        $request = new TestRequest();
        $response = $request->execute();
        $this->assertTrue(
            strpos($response->getContent(), 'EasyWiki') >= 0,
            'EasyWiki was not a word in the output'
        );
    }
}
