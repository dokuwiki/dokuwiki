<?php

class ap_enable extends ap_manage {

    var $enabled = array();

    function process() {
        global $plugin_protected;
        global $INPUT;

        $count_enabled = $count_disabled = 0;

        $this->enabled = $INPUT->arr('enabled');

        foreach ($this->manager->plugin_list as $plugin) {
            if (in_array($plugin, $plugin_protected)) continue;

            $new = in_array($plugin, $this->enabled);
            $old = !plugin_isdisabled($plugin);

            if ($new != $old) {
                switch ($new) {
                    // enable plugin
                    case true :
                        if(plugin_enable($plugin)){
                            msg(sprintf($this->lang['enabled'],$plugin),1);
                            $count_enabled++;
                        }else{
                            msg(sprintf($this->lang['notenabled'],$plugin),-1);
                        }
                        break;
                    case false:
                        if(plugin_disable($plugin)){
                            msg(sprintf($this->lang['disabled'],$plugin),1);
                            $count_disabled++;
                        }else{
                            msg(sprintf($this->lang['notdisabled'],$plugin),-1);
                        }
                        break;
                }
            }
        }

        // refresh plugins, including expiring any dokuwiki cache(s)
        if ($count_enabled || $count_disabled) {
            $this->refresh();
        }
    }

}

