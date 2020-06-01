<?php

namespace dokuwiki\plugin\config\core\Setting;

use dokuwiki\plugin\config\core\Configuration;

/**
 * A do-nothing class used to detect settings with no metadata entry.
 * Used internaly to hide undefined settings, and generate the undefined settings list.
 */
class SettingUndefined extends SettingHidden {

    protected $errorMessage = '_msg_setting_undefined';

    /** @inheritdoc */
    public function shouldHaveDefault() {
        return false;
    }

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        // determine the name the meta key would be called
        if(preg_match(
            '/^(?:plugin|tpl)' . Configuration::KEYMARKER . '.*?' . Configuration::KEYMARKER . '(.*)$/',
            $this->getKey(),
            $undefined_setting_match
        )) {
            $undefined_setting_key = $undefined_setting_match[1];
        } else {
            $undefined_setting_key = $this->getKey();
        }

        $label = '<span title="$meta[\'' . $undefined_setting_key . '\']">$' .
            'conf' . '[\'' . $this->getArrayKey() . '\']</span>';
        $input = $plugin->getLang($this->errorMessage);

        return array($label, $input);
    }

}
