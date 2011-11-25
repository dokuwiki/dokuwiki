<?php

class ap_manage {

    var $manager = NULL;
    var $lang = array();
    var $plugin = '';
    var $downloaded = array();

    function ap_manage(&$manager, $plugin) {
        $this->manager = & $manager;
        $this->plugin = $plugin;
        $this->lang = & $manager->lang;
    }

    function process() {
        return '';
    }

    function html() {
        print $this->manager->locale_xhtml('admin_plugin');
        $this->html_menu();
    }

    // build our standard menu
    function html_menu($listPlugins = true) {
        global $ID;

        ptln('<div class="pm_menu">');

        ptln('<div class="common">');
        ptln('  <h2>'.$this->lang['download'].'</h2>');
        ptln('  <form action="'.wl($ID).'" method="post">');
        ptln('    <fieldset class="hidden">',4);
        ptln('      <input type="hidden" name="do"   value="admin" />');
        ptln('      <input type="hidden" name="page" value="plugin" />');
        formSecurityToken();
        ptln('    </fieldset>');
        ptln('    <fieldset>');
        ptln('      <legend>'.$this->lang['download'].'</legend>');
        ptln('      <label for="dw__url">'.$this->lang['url'].'<input name="url" id="dw__url" class="edit" type="text" maxlength="200" /></label>');
        ptln('      <input type="submit" class="button" name="fn[download]" value="'.$this->lang['btn_download'].'" />');
        ptln('    </fieldset>');
        ptln('  </form>');
        ptln('</div>');

        if ($listPlugins) {
            ptln('<h2>'.$this->lang['manage'].'</h2>');

            ptln('<form action="'.wl($ID).'" method="post" class="plugins">');

            ptln('  <fieldset class="hidden">');
            ptln('    <input type="hidden" name="do"     value="admin" />');
            ptln('    <input type="hidden" name="page"   value="plugin" />');
            formSecurityToken();
            ptln('  </fieldset>');

            $this->html_pluginlist();

            ptln('  <fieldset class="buttons">');
            ptln('    <input type="submit" class="button" name="fn[enable]" value="'.$this->lang['btn_enable'].'" />');
            ptln('  </fieldset>');

            //            ptln('  </div>');
            ptln('</form>');
        }

        ptln('</div>');
    }

    function html_pluginlist() {
        global $ID;
        global $plugin_protected;

        foreach ($this->manager->plugin_list as $plugin) {

            $disabled = plugin_isdisabled($plugin);
            $protected = in_array($plugin,$plugin_protected);

            $checked = ($disabled) ? '' : ' checked="checked"';
            $check_disabled = ($protected) ? ' disabled="disabled"' : '';

            // determine display class(es)
            $class = array();
            if (in_array($plugin, $this->downloaded)) $class[] = 'new';
            if ($disabled) $class[] = 'disabled';
            if ($protected) $class[] = 'protected';

            $class = count($class) ? ' class="'.join(' ', $class).'"' : '';

            ptln('    <fieldset'.$class.'>');
            ptln('      <legend>'.$plugin.'</legend>');
            ptln('      <input type="checkbox" class="enable" name="enabled[]" id="dw__p_'.$plugin.'" value="'.$plugin.'"'.$checked.$check_disabled.' />');
            ptln('      <h3 class="legend"><label for="dw__p_'.$plugin.'">'.$plugin.'</label></h3>');

            $this->html_button($plugin, 'info', false, 6);
            if (in_array('settings', $this->manager->functions)) {
                $this->html_button($plugin, 'settings', !@file_exists(DOKU_PLUGIN.$plugin.'/settings.php'), 6);
            }
            $this->html_button($plugin, 'update', !$this->plugin_readlog($plugin, 'url'), 6);
            $this->html_button($plugin, 'delete', $protected, 6);

            ptln('    </fieldset>');
        }
    }

    function html_button($plugin, $btn, $disabled=false, $indent=0) {
        $disabled = ($disabled) ? 'disabled="disabled"' : '';
        ptln('<input type="submit" class="button" '.$disabled.' name="fn['.$btn.']['.$plugin.']" value="'.$this->lang['btn_'.$btn].'" />',$indent);
    }

    /**
     *  Refresh plugin list
     */
    function refresh() {
        global $config_cascade;

        // expire dokuwiki caches
        // touching local.php expires wiki page, JS and CSS caches
        @touch(reset($config_cascade['main']['local']));

        // update latest plugin date - FIXME
        global $ID;
        send_redirect(wl($ID,array('do'=>'admin','page'=>'plugin'),true, '&'));
    }

    /**
     * Write a log entry to the given target directory
     */
    function plugin_writelog($target, $cmd, $data) {

        $file = $target.'/manager.dat';

        switch ($cmd) {
            case 'install' :
                $url = $data[0];
                $date = date('r');
                if (!$fp = @fopen($file, 'w')) return;
                fwrite($fp, "installed=$date\nurl=$url\n");
                fclose($fp);
                break;

            case 'update' :
                $date = date('r');
                if (!$fp = @fopen($file, 'a')) return;
                fwrite($fp, "updated=$date\n");
                fclose($fp);
                break;
        }
    }

    function plugin_readlog($plugin, $field) {
        static $log = array();
        $file = DOKU_PLUGIN.plugin_directory($plugin).'/manager.dat';

        if (!isset($log[$plugin])) {
            $tmp = @file_get_contents($file);
            if (!$tmp) return '';
            $log[$plugin] = & $tmp;
        }

        if ($field == 'ALL') {
            return $log[$plugin];
        }

        $match = array();
        if (preg_match_all('/'.$field.'=(.*)$/m',$log[$plugin], $match))
            return implode("\n", $match[1]);

        return '';
    }

    /**
     * delete, with recursive sub-directory support
     */
    function dir_delete($path) {
        if (!is_string($path) || $path == "") return false;

        if (is_dir($path) && !is_link($path)) {
            if (!$dh = @opendir($path)) return false;

            while ($f = readdir($dh)) {
                if ($f == '..' || $f == '.') continue;
                $this->dir_delete("$path/$f");
            }

            closedir($dh);
            return @rmdir($path);
        } else {
            return @unlink($path);
        }

        return false;
    }


}
