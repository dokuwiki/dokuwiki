<?php

class action_plugin_testing extends DokuWiki_Action_Plugin {
    function register(&$controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER', $this, 'dokuwikiStarted');
    }

    function dokuwikiStarted() {
        $param = array();
        trigger_event('TESTING_PLUGIN_INSTALLED', $param);
        msg('hohoho');
    }
}
