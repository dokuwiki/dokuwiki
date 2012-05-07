<?php

/**
 * @group integration
 */
class InttestsBasicTest extends DokuWikiTest {
    /**
     * Execute the simplest possible request and expect
     * a dokuwiki page which obviously has the word "DokuWiki"
     * in it somewhere.
     */
    function testSimpleRun() {
        $request = new TestRequest();

        $response = $request->execute();

        $this->assertTrue(
            strpos($response->getContent(), 'DokuWiki') >= 0,
            'DokuWiki was not a word in the output'
        );
    }
}
