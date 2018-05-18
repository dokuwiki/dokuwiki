<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_disableactions
 */
class SettingDisableactions extends SettingMulticheckbox {

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        global $lang;

        // make some language adjustments (there must be a better way)
        // transfer some DokuWiki language strings to the plugin
        $plugin->addLang($this->key . '_revisions', $lang['btn_revs']);
        foreach($this->choices as $choice) {
            if(isset($lang['btn_' . $choice])) $plugin->addLang($this->key . '_' . $choice, $lang['btn_' . $choice]);
        }

        return parent::html($plugin, $echo);
    }
}
