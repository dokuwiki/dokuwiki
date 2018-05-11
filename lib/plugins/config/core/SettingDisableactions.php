<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_disableactions
 */
class SettingDisableactions extends SettingMulticheckbox {

    /**
     * Build html for label and input of setting
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @param bool $echo true: show inputted value, when error occurred, otherwise the stored setting
     * @return array with content array(string $label_html, string $input_html)
     */
    public function html(\admin_plugin_config $plugin, $echo = false) {
        global $lang;

        // make some language adjustments (there must be a better way)
        // transfer some DokuWiki language strings to the plugin
        $plugin->addLang($this->_key . '_revisions', $lang['btn_revs']);
        foreach($this->_choices as $choice) {
            if(isset($lang['btn_' . $choice])) $plugin->addLang($this->_key . '_' . $choice, $lang['btn_' . $choice]);
        }

        return parent::html($plugin, $echo);
    }
}
