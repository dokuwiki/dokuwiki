<?php
/**
 * Plugin for testing the test system
 *
 * This plugin doesn't really do anything and should always be disabled
 *
 * @author Tobias Sarnowski <tobias@trustedco.de>
 */
class action_plugin_testing extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'dokuwikiStarted');
    }

    function dokuwikiStarted() {
        $param = array();
        trigger_event('TESTING_PLUGIN_INSTALLED', $param);
        msg('The testing plugin is enabled and should be disabled.',-1);
    }
}
