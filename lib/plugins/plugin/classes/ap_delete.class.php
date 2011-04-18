<?php
class ap_delete extends ap_manage {

    function process() {

        if (!$this->dir_delete(DOKU_PLUGIN.plugin_directory($this->manager->plugin))) {
            $this->manager->error = sprintf($this->lang['error_delete'],$this->manager->plugin);
        } else {
            msg(sprintf($this->lang['deleted'],$this->plugin));
            $this->refresh();
        }
    }

    function html() {
        parent::html();

        ptln('<div class="pm_info">');
        ptln('<h2>'.$this->lang['deleting'].'</h2>');

        if ($this->manager->error) {
            ptln('<div class="error">'.str_replace("\n","<br />",$this->manager->error).'</div>');
        } else {
            ptln('<p>'.sprintf($this->lang['deleted'],$this->plugin).'</p>');
        }
        ptln('</div>');
    }
}

