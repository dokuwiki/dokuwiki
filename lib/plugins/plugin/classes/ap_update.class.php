<?php
require_once(DOKU_PLUGIN."/plugin/classes/ap_download.class.php");
class ap_update extends ap_download {

    var $overwrite = true;

    function process() {
        global $lang;

        $plugin_url = $this->plugin_readlog($this->plugin, 'url');
        $this->download($plugin_url, $this->overwrite);
        return '';
    }

    function html() {
        parent::html();

        ptln('<div class="pm_info">');
        ptln('<h2>'.$this->lang['updating'].'</h2>');

        if ($this->manager->error) {
            ptln('<div class="error">'.str_replace("\n","<br />", $this->manager->error).'</div>');
        } else if (count($this->downloaded) == 1) {
            ptln('<p>'.sprintf($this->lang['updated'],$this->downloaded[0]).'</p>');
        } else if (count($this->downloaded)) {   // more than one plugin in the download
            ptln('<p>'.$this->lang['updates'].'</p>');
            ptln('<ul>');
            foreach ($this->downloaded as $plugin) {
                ptln('<li><div class="li">'.$plugin.'</div></li>',2);
            }
            ptln('</ul>');
        } else {        // none found in download
            ptln('<p>'.$this->lang['update_none'].'</p>');
        }
        ptln('</div>');
    }
}

