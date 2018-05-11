<?php
/**
 * additional setting classes specific to these settings
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_renderer
 */
class SettingRenderer extends SettingMultichoice {
    protected $_prompts = array();
    protected $_format = null;

    /**
     * Receives current values for the setting $key
     *
     * @param mixed $default default setting value
     * @param mixed $local local setting value
     * @param mixed $protected protected setting value
     */
    public function initialize($default, $local, $protected) {
        $format = $this->_format;

        foreach(plugin_list('renderer') as $plugin) {
            $renderer = plugin_load('renderer', $plugin);
            if(method_exists($renderer, 'canRender') && $renderer->canRender($format)) {
                $this->_choices[] = $plugin;

                $info = $renderer->getInfo();
                $this->_prompts[$plugin] = $info['name'];
            }
        }

        parent::initialize($default, $local, $protected);
    }

    /**
     * Build html for label and input of setting
     *
     * @param \admin_plugin_config $plugin object of config plugin
     * @param bool $echo true: show inputted value, when error occurred, otherwise the stored setting
     * @return array with content array(string $label_html, string $input_html)
     */
    public function html(\admin_plugin_config $plugin, $echo = false) {

        // make some language adjustments (there must be a better way)
        // transfer some plugin names to the config plugin
        foreach($this->_choices as $choice) {
            if(!$plugin->getLang($this->_key . '_o_' . $choice)) {
                if(!isset($this->_prompts[$choice])) {
                    $plugin->addLang(
                        $this->_key . '_o_' . $choice,
                        sprintf($plugin->getLang('renderer__core'), $choice)
                    );
                } else {
                    $plugin->addLang(
                        $this->_key . '_o_' . $choice,
                        sprintf($plugin->getLang('renderer__plugin'), $this->_prompts[$choice])
                    );
                }
            }
        }
        return parent::html($plugin, $echo);
    }
}
