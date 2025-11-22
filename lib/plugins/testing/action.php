<?php

use easywiki\Extension\ActionPlugin;
use easywiki\Extension\EventHandler;
use easywiki\Extension\Event;

/**
 * Plugin for testing the test system
 *
 * This plugin doesn't really do anything and should always be disabled
 *
 * @author Tobias Sarnowski <tobias@trustedco.de>
 */
class action_plugin_testing extends ActionPlugin
{
    /** @inheritdoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('EASYWIKI_STARTED', 'AFTER', $this, 'easywikiStarted');
    }

    public function easywikiStarted()
    {
        $param = [];
        Event::createAndTrigger('TESTING_PLUGIN_INSTALLED', $param);
        msg('The testing plugin is enabled and should be disabled.', -1);
    }
}
