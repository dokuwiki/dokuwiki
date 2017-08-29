<?php

/**
 * @group ajax
 */
class ajax_requests_test extends DokuWikiTest {

    /**
     * DataProvider for the builtin Ajax calls
     *
     * @return array
     */
    public function defaultCalls() {
        return [
            // TODO: better logic and DOM walks
            // Call           | POST     |   regexp pattern to match
            [ 'linkwiz',      ['q' => ''], '/^<div class="odd type_d/' ],
            [ 'suggestions',  ['q' => ''], null ],
            [ 'lock',         ['id' => ''], null ],
            [ 'draftdel',     ['id' => ''], null ],
            [ 'medians',      ['ns' => 'some:ns'], null ],
            [ 'medialist',    ['ns' => '', 'recent' => '', 'do' => ''], null ],
            [ 'mediadetails', ['image' => ''], null ],
            [ 'mediadiff',    ['image' => ''], null ],
            [ 'mediaupload',  ['mediaid' => '', 'qqfile' => '' ], null ], // $_FILES
            [ 'index',        ['idx' => ''], null ],
            [ 'linkwiz',      ['q' => ''], null ],
        ];
    }

    /**
     * @dataProvider defaultCalls
     * @param string $call
     * @param array $post
     * @param string $regexp
     */
    public function test_defaultCallsExist($call, $post, $regexp) {

        $request = new TestRequest();
        $response = $request->post(['call'=> $call]+$post, '/lib/exe/ajax.php');
        $this->assertNotEquals("AJAX call '$call' unknown!\n", $response->getContent());

        if (!empty($regexp)) {
            $this->assertRegExp($regexp, $response->getContent());
        }
    }

    public function test_CallNotProvided() {
        $request = new TestRequest();
        $response = $request->post([], '/lib/exe/ajax.php');
        $this->assertEquals('', $response->getContent());
    }

    public function test_UnknownCall() {
        $call = 'unknownCALL';
        $request = new TestRequest();
        $response = $request->post(['call'=> $call], '/lib/exe/ajax.php');
        $this->assertEquals("AJAX call '$call' unknown!\n", $response->getContent());
    }


    public function test_EventOnUnknownCall() {
        global $EVENT_HANDLER;
        $call = 'unknownCALL';
        $request = new TestRequest();

        // referenced data from event hook
        $hookTriggered = false;
        $eventDataTriggered = '';
        $dataTriggered = '';
        $postTriggered = '';

        $hookTriggered_AFTER = false;
        $eventDataTriggered_AFTER  = '';
        $dataTriggered_AFTER  = '';
        $postTriggered_AFTER  = '';

        $EVENT_HANDLER->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', null,
            function($event, $data) use (&$hookTriggered, &$dataTriggered, &$eventDataTriggered, &$postTriggered) {
                /** @var Doku_Event $event */
                $hookTriggered = true;
                $dataTriggered = $data;
                $eventDataTriggered = $event->data;
                $postTriggered = $GLOBALS['INPUT']->post->str('q');
                $event->preventDefault();
                $event->stopPropagation();
                echo "captured event BEFORE\n";
            }, 'some passed data'
        );

        $EVENT_HANDLER->register_hook('AJAX_CALL_UNKNOWN', 'AFTER', null,
            function($event, $data) use (&$hookTriggered_AFTER , &$dataTriggered_AFTER , &$eventDataTriggered_AFTER , &$postTriggered_AFTER ) {
                /** @var Doku_Event $event */
                $hookTriggered_AFTER  = true;
                $dataTriggered_AFTER  = $data;
                $eventDataTriggered_AFTER  = $event->data;
                $postTriggered_AFTER  = $GLOBALS['INPUT']->post->str('q');
                $event->preventDefault();
                $event->stopPropagation();
                echo "captured event AFTER";
            }, 'some passed data AFTER'
        );


        $response = $request->post(['call'=> $call, 'q' => 'some-post-param'], '/lib/exe/ajax.php');

        // BEFORE
        $this->assertEquals(true, $hookTriggered, 'Testing plugin did not trigger!');
        $this->assertEquals('some passed data', $dataTriggered);
        $this->assertEquals($call, $eventDataTriggered, 'Must pass call name as event data');
        $this->assertEquals('some-post-param', $postTriggered);

        // AFTER
        $this->assertEquals(true, $hookTriggered_AFTER, 'Testing plugin did not trigger!');
        $this->assertEquals('some passed data AFTER', $dataTriggered_AFTER);
        $this->assertEquals($call, $eventDataTriggered_AFTER, 'Must pass call name as event data');
        $this->assertEquals('some-post-param', $postTriggered_AFTER);

        //output
        $this->assertEquals("captured event BEFORE\ncaptured event AFTER", $response->getContent());

    }
}
