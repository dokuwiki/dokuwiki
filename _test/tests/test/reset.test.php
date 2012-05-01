<?php

/**
 * @group integration
 */
class InttestsScopeTest extends DokuWikiTest {

    public $triggered = false;

    function testFirstRun(){
        global $conf;
        $conf['foo'] = 'bar';

        global $EVENT_HANDLER;
        $self = $this;
        $EVENT_HANDLER->register_hook('DOKUWIKI_STARTED', 'AFTER', null,
            function() use ($self) {
                $self->triggered = true;
            }
        );
        $request = new TestRequest();
        $request->execute();
        $this->assertTrue($this->triggered);
    }

    /**
     * @depends testFirstRun
     */
    function testSecondRun(){
        global $conf;
        $this->assertFalse(isset($conf['foo']), 'conf setting');

        $request = new TestRequest();
        $request->execute();

        $this->assertFalse($this->triggered, 'trigger');
    }
}
