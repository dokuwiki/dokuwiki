<?php

use dokuwiki\Extension\Event;

/**
 * Plugin for testing the test system
 *
 * This plugin doesn't really do anything and should always be disabled
 *
 * @author Tobias Sarnowski <tobias@trustedco.de>
 */
class action_plugin_testing extends DokuWiki_Action_Plugin {

    /** @inheritdoc */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'dokuwikiStarted');
    }

    public function dokuwikiStarted() {
        $param = array();
        Event::createAndTrigger('TESTING_PLUGIN_INSTALLED', $param);
        msg('The testing plugin is enabled and should be disabled.',-1);
    }
}
