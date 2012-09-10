<?php

/**
 * @group integration
 */
class InttestsGlobalsTest extends DokuWikiTest {

    /**
     * every request should be with its own variables
     */
    function testFirstRun() {
        global $EVENT_HANDLER;

        $request = new TestRequest();
        $request->setServer('testvar', true);

        $self = $this;
        $EVENT_HANDLER->register_hook('TPL_CONTENT_DISPLAY', 'AFTER', null,
            function() use ($self) {
                $self->assertTrue($_SERVER['testvar'], 'Server variable not set correctly: testvar');
                $self->assertEquals('87.142.120.6', $_SERVER['REMOTE_ADDR'], 'Server variable not set correctly: REMOTE_ADDR');
                $_SERVER['tmpvar'] = true;
            }
        );

        $request->execute();
    }

    /**
     * @depends testFirstRun
     */
    function testSecondRun() {
        global $EVENT_HANDLER;

        $request = new TestRequest();
        $request->setServer('testvar', false);

        $self = $this;
        $EVENT_HANDLER->register_hook('TPL_CONTENT_DISPLAY', 'AFTER', null,
            function() use ($self) {
                $self->assertFalse($_SERVER['testvar'], 'Server variable not set correctly: testvar');
                $self->assertEquals('87.142.120.6', $_SERVER['REMOTE_ADDR'], 'Server variable not set correctly: REMOTE_ADDR');
                $self->assertFalse(isset($_SERVER['tmpvar']));
            }
        );

        $request->execute();
    }
}
