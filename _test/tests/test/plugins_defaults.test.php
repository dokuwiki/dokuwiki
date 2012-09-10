<?php

/**
 * @group integration
 */
class InttestsPluginsDefaultTest extends DokuWikiTest {

    function testTestingPluginDisabledDefault() {
        global $EVENT_HANDLER;

        $request = new TestRequest();
        $hookTriggered = false;

        $EVENT_HANDLER->register_hook('TESTING_PLUGIN_INSTALLED', 'AFTER', null,
            function() use (&$hookTriggered) {
                $hookTriggered = true;
            }
        );

        $request->execute();

        $this->assertFalse($hookTriggered, 'Testing plugin did trigger!');
    }
}
