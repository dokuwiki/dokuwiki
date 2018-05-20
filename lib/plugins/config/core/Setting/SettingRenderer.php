<?php
/**
 * additional setting classes specific to these settings
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_renderer
 */
class SettingRenderer extends SettingMultichoice {
    protected $prompts = array();
    protected $format = null;

    /** @inheritdoc */
    public function initialize($default = null, $local = null, $protected = null) {
        $format = $this->format;

        foreach(plugin_list('renderer') as $plugin) {
            $renderer = plugin_load('renderer', $plugin);
            if(method_exists($renderer, 'canRender') && $renderer->canRender($format)) {
                $this->choices[] = $plugin;

                $info = $renderer->getInfo();
                $this->prompts[$plugin] = $info['name'];
            }
        }

        parent::initialize($default, $local, $protected);
    }

    /** @inheritdoc */
    public function html(\admin_plugin_config $plugin, $echo = false) {

        // make some language adjustments (there must be a better way)
        // transfer some plugin names to the config plugin
        foreach($this->choices as $choice) {
            if(!$plugin->getLang($this->key . '_o_' . $choice)) {
                if(!isset($this->prompts[$choice])) {
                    $plugin->addLang(
                        $this->key . '_o_' . $choice,
                        sprintf($plugin->getLang('renderer__core'), $choice)
                    );
                } else {
                    $plugin->addLang(
                        $this->key . '_o_' . $choice,
                        sprintf($plugin->getLang('renderer__plugin'), $this->prompts[$choice])
                    );
                }
            }
        }
        return parent::html($plugin, $echo);
    }
}
