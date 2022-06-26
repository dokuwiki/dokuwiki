<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_authtype
 */
class SettingAuthtype extends SettingMultichoice {

    /** @inheritdoc */
    public function initialize($default = null, $local = null, $protected = null) {
        /** @var $plugin_controller \dokuwiki\Extension\PluginController */
        global $plugin_controller;

        // retrieve auth types provided by plugins
        foreach($plugin_controller->getList('auth') as $plugin) {
            $this->choices[] = $plugin;
        }

        parent::initialize($default, $local, $protected);
    }

    /** @inheritdoc */
    public function update($input) {
        /** @var $plugin_controller \dokuwiki\Extension\PluginController */
        global $plugin_controller;

        // is an update possible/requested?
        $local = $this->local;                       // save this, parent::update() may change it
        if(!parent::update($input)) return false;    // nothing changed or an error caught by parent
        $this->local = $local;                       // restore original, more error checking to come

        // attempt to load the plugin
        $auth_plugin = $plugin_controller->load('auth', $input);

        // @TODO: throw an error in plugin controller instead of returning null
        if(is_null($auth_plugin)) {
            $this->error = true;
            msg('Cannot load Auth Plugin "' . $input . '"', -1);
            return false;
        }

        // verify proper instantiation (is this really a plugin?) @TODO use instanceof? implement interface?
        if(is_object($auth_plugin) && !method_exists($auth_plugin, 'getPluginName')) {
            $this->error = true;
            msg('Cannot create Auth Plugin "' . $input . '"', -1);
            return false;
        }

        // did we change the auth type? logout
        global $conf;
        if($conf['authtype'] != $input) {
            msg('Authentication system changed. Please re-login.');
            auth_logoff();
        }

        $this->local = $input;
        return true;
    }
}
