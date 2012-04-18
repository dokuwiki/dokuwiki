<?php

/**
 * @group integration
 */
class InttestsHooksTest extends DokuWikiTest {

    function testHookTriggering() {
        global $EVENT_HANDLER;

        $request = new TestRequest();
        $hookTriggered = false;

        $EVENT_HANDLER->register_hook('TPL_CONTENT_DISPLAY', 'AFTER', null,
            function() use (&$hookTriggered) {
                $hookTriggered = true;
            }
        );

        $request->execute();

        $this->assertTrue($hookTriggered, 'Hook was not triggered as expected!');
    }
}
