<?php

/**
 * @group integration
 */
class InttestsPluginsTest extends DokuWikiTest {

    function testTestingPluginEnabled() {
        global $EVENT_HANDLER, $plugin_controller;

        $this->assertTrue(
            $plugin_controller->enable('testing'),
            'Could not enable testing plugin.'
        );

        $request = new TestRequest();
        $hookTriggered = false;

        $EVENT_HANDLER->register_hook('TESTING_PLUGIN_INSTALLED', 'AFTER', null,
            function() use (&$hookTriggered) {
                $hookTriggered = true;
            }
        );

        $request->execute();

        $this->assertTrue($hookTriggered, 'Testing plugin did not trigger!');
    }

    /**
     * @depends testTestingPluginEnabled
     */
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
