<?php

/**
 * This tests if event handlers can trigger the same event again.
 * This is used by plugins that modify cache handling and use metadata
 * for checking cache validity which triggers another cache use event.
 */
class events_nested_test extends DokuWikiTest {
    function test_nested_events() {
        global $EVENT_HANDLER;
        $firstcount = 0;
        $secondcount = 0;

        $EVENT_HANDLER->register_hook('NESTED_EVENT', 'BEFORE', null,
            function() use (&$firstcount) {
                $firstcount++;
                if ($firstcount == 1) {
                    $param = array();
                    trigger_event('NESTED_EVENT', $param);
                }
            }
        );

        $EVENT_HANDLER->register_hook('NESTED_EVENT', 'BEFORE', null,
            function() use (&$secondcount) {
                $secondcount++;
            }
        );

        $param = array();
        trigger_event('NESTED_EVENT', $param);

        $this->assertEquals(2, $firstcount);
        $this->assertEquals(2, $secondcount);
    }
}
