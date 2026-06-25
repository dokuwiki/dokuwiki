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
            $this->assertMatchesRegularExpression($regexp, $response->getContent());
        }
    }

    /**
     * callMediaupload must normalize the namespace with cleanID() before it is used.
     *
     * regression test for XSS reflection and passing unclened data to the ACL check
     */
    public function test_mediaupload_reflects_cleaned_namespace() {
        $request = new TestRequest();
        $response = $request->post(
            ['call' => 'mediaupload', 'ns' => 'Foo"><script>x</script>'],
            '/lib/exe/ajax.php'
        );

        $result = json_decode($response->getContent(), true);
        $this->assertIsArray($result);
        $this->assertSame(
            'foo_script_x_script',
            $result['ns'],
            'the raw namespace must be cleaned before it is used'
        );
    }

    /**
     * Compute a security token for the given user, matching what same-process code (the
     * request) computes for the same user and the shared test session, without leaking the
     * temporary REMOTE_USER into the global state other tests rely on.
     *
     * @param string $user
     * @return string
     */
    protected function validTokenFor($user) {
        global $INPUT;
        $oldServer = $_SERVER;
        $oldInput = $INPUT;
        $_SERVER['REMOTE_USER'] = $user;
        $INPUT = new \dokuwiki\Input\Input();
        $token = getSecurityToken();
        $_SERVER = $oldServer;
        $INPUT = $oldInput;
        return $token;
    }

    /**
     * The happy path: a logged in user with a valid token takes the page lock and the
     * posted text is stored as a retrievable draft.
     *
     * Doubles as the "valid token is accepted" case: if the CSRF gate wrongly rejected a
     * valid token the lock would stay '0' and this test would fail.
     */
    public function test_lock_takesLockAndSavesDraft() {
        $id = 'lock:happy';
        $text = 'some draft text';

        $request = new TestRequest();
        $request->setServer('REMOTE_USER', 'testuser');
        $response = $request->post(
            ['call' => 'lock', 'id' => $id, 'sectok' => $this->validTokenFor('testuser'), 'wikitext' => $text],
            '/lib/exe/ajax.php'
        );

        $result = json_decode($response->getContent(), true);
        $this->assertIsArray($result);
        $this->assertSame([], $result['errors']);
        $this->assertEquals('1', $result['lock'], 'the lock must be taken');
        $this->assertNotEmpty($result['draft'], 'a draft-saved message must be returned');

        // the lock is actually held on disk by the user
        $this->assertFileExists(wikiLockFN($id));
        $this->assertEquals('testuser', io_readFile(wikiLockFN($id)));

        // the draft is retrievable and round-trips the posted text
        $draft = new \dokuwiki\Draft($id, 'testuser');
        $this->assertTrue($draft->isDraftAvailable());
        $this->assertStringContainsString($text, $draft->getDraftText());
    }

    /**
     * The lock call takes a page lock and writes a draft, so for a logged in user it
     * must be protected against CSRF. A request carrying an invalid security token must
     * be rejected before any lock is taken.
     */
    public function test_lock_rejectsInvalidSecurityToken() {
        $id = 'lock:reject';

        $request = new TestRequest();
        $request->setServer('REMOTE_USER', 'testuser');
        $response = $request->post(
            ['call' => 'lock', 'id' => $id, 'sectok' => 'not-the-real-token', 'wikitext' => 'x'],
            '/lib/exe/ajax.php'
        );

        $result = json_decode($response->getContent(), true);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['errors'], 'an invalid security token must be rejected');
        $this->assertEquals('0', $result['lock'], 'no lock may be taken on a rejected request');
        $this->assertFileDoesNotExist(wikiLockFN($id), 'no lock file may be written on a rejected request');
    }

    /**
     * When the user lacks write permission the call refuses to lock or save a draft.
     */
    public function test_lock_deniedWhenNotWritable() {
        global $conf, $AUTH_ACL;
        $id = 'lock:denied';

        $oldAcl = $AUTH_ACL;
        $conf['useacl'] = 1;
        $AUTH_ACL = ['*                  @ALL           0']; // deny everyone

        try {
            // anonymous: no security token needed, the write ACL is what must block this
            $request = new TestRequest();
            $response = $request->post(
                ['call' => 'lock', 'id' => $id, 'wikitext' => 'x'],
                '/lib/exe/ajax.php'
            );

            $result = json_decode($response->getContent(), true);
            $this->assertIsArray($result);
            $this->assertNotEmpty($result['errors'], 'a denied write must be reported');
            $this->assertEquals('0', $result['lock']);
            $this->assertSame('', $result['draft'], 'no draft may be saved when not writable');
            $this->assertFileDoesNotExist(wikiLockFN($id));
        } finally {
            $AUTH_ACL = $oldAcl;
        }
    }

    /**
     * A lock already held by someone else must not be reported as freshly taken and must
     * be left untouched.
     */
    public function test_lock_doesNotStealForeignLock() {
        $id = 'lock:foreign';

        // someone else already holds the lock
        io_saveFile(wikiLockFN($id), 'someoneelse');

        $request = new TestRequest();
        $request->setServer('REMOTE_USER', 'testuser');
        $response = $request->post(
            ['call' => 'lock', 'id' => $id, 'sectok' => $this->validTokenFor('testuser')],
            '/lib/exe/ajax.php'
        );

        $result = json_decode($response->getContent(), true);
        $this->assertIsArray($result);
        $this->assertEquals('0', $result['lock'], 'a foreign lock must not be reported as freshly taken');
        $this->assertEquals('someoneelse', io_readFile(wikiLockFN($id)), 'the foreign lock must be left untouched');
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
