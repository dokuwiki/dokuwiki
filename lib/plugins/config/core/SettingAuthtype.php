<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_authtype
 */
class SettingAuthtype extends SettingMultichoice {

    /**
     * Receives current values for the setting $key
     *
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default, $local, $protected) {
        /** @var $plugin_controller \Doku_Plugin_Controller */
        global $plugin_controller;

        // retrieve auth types provided by plugins
        foreach($plugin_controller->getList('auth') as $plugin) {
            $this->_choices[] = $plugin;
        }

        parent::initialize($default, $local, $protected);
    }

    /**
     * update changed setting with user provided value $input
     * - if changed value fails error check, save it to $this->_input (to allow echoing later)
     * - if changed value passes error check, set $this->_local to the new value
     *
     * @param  mixed $input the new value
     * @return boolean          true if changed, false otherwise (also on error)
     */
    public function update($input) {
        /** @var $plugin_controller \Doku_Plugin_Controller */
        global $plugin_controller;

        // is an update possible/requested?
        $local = $this->_local;                       // save this, parent::update() may change it
        if(!parent::update($input)) return false;    // nothing changed or an error caught by parent
        $this->_local = $local;                       // restore original, more error checking to come

        // attempt to load the plugin
        $auth_plugin = $plugin_controller->load('auth', $input);

        // @TODO: throw an error in plugin controller instead of returning null
        if(is_null($auth_plugin)) {
            $this->_error = true;
            msg('Cannot load Auth Plugin "' . $input . '"', -1);
            return false;
        }

        // verify proper instantiation (is this really a plugin?) @TODO use instanceof? implement interface?
        if(is_object($auth_plugin) && !method_exists($auth_plugin, 'getPluginName')) {
            $this->_error = true;
            msg('Cannot create Auth Plugin "' . $input . '"', -1);
            return false;
        }

        // did we change the auth type? logout
        global $conf;
        if($conf['authtype'] != $input) {
            msg('Authentication system changed. Please re-login.');
            auth_logoff();
        }

        $this->_local = $input;
        return true;
    }
}
