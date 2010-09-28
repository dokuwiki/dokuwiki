<?php

class ap_info extends ap_manage {

    var $plugin_info = array();        // the plugin itself
    var $details = array();            // any component plugins

    function process() {

        // sanity check
        if (!$this->manager->plugin) { return; }

        $component_list = $this->get_plugin_components($this->manager->plugin);
        usort($component_list, array($this,'component_sort'));


        foreach ($component_list as $component) {
            if (($obj = &plugin_load($component['type'],$component['name'],false,true)) === NULL) continue;

            $compname = explode('_',$component['name']);
            if($compname[1]){
                $compname = '['.$compname[1].']';
            }else{
                $compname = '';
            }

            $this->details[] = array_merge(
                                    $obj->getInfo(),
                                    array(
                                        'type' => $component['type'],
                                        'compname' => $compname
                                    ));
            unset($obj);
        }

        // review details to simplify things
        foreach($this->details as $info) {
            foreach($info as $item => $value) {
                if (!isset($this->plugin_info[$item])) { $this->plugin_info[$item] = $value; continue; }
                if ($this->plugin_info[$item] != $value) $this->plugin_info[$item] = '';
            }
        }
    }

    function html() {

        // output the standard menu stuff
        parent::html();

        // sanity check
        if (!$this->manager->plugin) { return; }

        ptln('<div class="pm_info">');
        ptln("<h2>".$this->manager->getLang('plugin')." {$this->manager->plugin}</h2>");

        // collect pertinent information from the log
        $installed = $this->plugin_readlog($this->manager->plugin, 'installed');
        $source = $this->plugin_readlog($this->manager->plugin, 'url');
        $updated = $this->plugin_readlog($this->manager->plugin, 'updated');
        if (strrpos($updated, "\n") !== false) $updated = substr($updated, strrpos($updated, "\n")+1);

        ptln("<dl>",2);
        ptln("<dt>".$this->manager->getLang('source').'</dt><dd>'.($source ? $source : $this->manager->getLang('unknown'))."</dd>",4);
        ptln("<dt>".$this->manager->getLang('installed').'</dt><dd>'.($installed ? $installed : $this->manager->getLang('unknown'))."</dd>",4);
        if ($updated) ptln("<dt>".$this->manager->getLang('lastupdate').'</dt><dd>'.$updated."</dd>",4);
        ptln("</dl>",2);

        if (count($this->details) == 0) {
            ptln("<p>".$this->manager->getLang('noinfo')."</p>",2);
        } else {

            ptln("<dl>",2);
            if ($this->plugin_info['name']) ptln("<dt>".$this->manager->getLang('name')."</dt><dd>".$this->out($this->plugin_info['name'])."</dd>",4);
            if ($this->plugin_info['date']) ptln("<dt>".$this->manager->getLang('date')."</dt><dd>".$this->out($this->plugin_info['date'])."</dd>",4);
            if ($this->plugin_info['type']) ptln("<dt>".$this->manager->getLang('type')."</dt><dd>".$this->out($this->plugin_info['type'])."</dd>",4);
            if ($this->plugin_info['desc']) ptln("<dt>".$this->manager->getLang('desc')."</dt><dd>".$this->out($this->plugin_info['desc'])."</dd>",4);
            if ($this->plugin_info['author']) ptln("<dt>".$this->manager->getLang('author')."</dt><dd>".$this->manager->email($this->plugin_info['email'], $this->plugin_info['author'])."</dd>",4);
            if ($this->plugin_info['url']) ptln("<dt>".$this->manager->getLang('www')."</dt><dd>".$this->manager->external_link($this->plugin_info['url'], '', 'urlextern')."</dd>",4);
            ptln("</dl>",2);

            if (count($this->details) > 1) {
                ptln("<h3>".$this->manager->getLang('components')."</h3>",2);
                ptln("<div>",2);

                foreach ($this->details as $info) {

                    ptln("<dl>",4);
                    ptln("<dt>".$this->manager->getLang('name')."</dt><dd>".$this->out($info['name'].' '.$info['compname'])."</dd>",6);
                    if (!$this->plugin_info['date']) ptln("<dt>".$this->manager->getLang('date')."</dt><dd>".$this->out($info['date'])."</dd>",6);
                    if (!$this->plugin_info['type']) ptln("<dt>".$this->manager->getLang('type')."</dt><dd>".$this->out($info['type'])."</dd>",6);
                    if (!$this->plugin_info['desc']) ptln("<dt>".$this->manager->getLang('desc')."</dt><dd>".$this->out($info['desc'])."</dd>",6);
                    if (!$this->plugin_info['author']) ptln("<dt>".$this->manager->getLang('author')."</dt><dd>".$this->manager->email($info['email'], $info['author'])."</dd>",6);
                    if (!$this->plugin_info['url']) ptln("<dt>".$this->manager->getLang('www')."</dt><dd>".$this->manager->external_link($info['url'], '', 'urlextern')."</dd>",6);
                    ptln("</dl>",4);

                }
                ptln("</div>",2);
            }
        }
        ptln("</div>");
    }

    // simple output filter, make html entities safe and convert new lines to <br />
    function out($text) {
        return str_replace("\n",'<br />',htmlspecialchars($text));
    }


    /**
     * return a list (name & type) of all the component plugins that make up this plugin
     *
     * @todo can this move to pluginutils?
     */
    function get_plugin_components($plugin) {

        global $plugin_types;

        $components = array();
        $path = DOKU_PLUGIN.plugin_directory($plugin).'/';

        foreach ($plugin_types as $type) {
            if (@file_exists($path.$type.'.php')) { $components[] = array('name'=>$plugin, 'type'=>$type); continue; }

            if ($dh = @opendir($path.$type.'/')) {
                while (false !== ($cp = readdir($dh))) {
                    if ($cp == '.' || $cp == '..' || strtolower(substr($cp,-4)) != '.php') continue;

                    $components[] = array('name'=>$plugin.'_'.substr($cp, 0, -4), 'type'=>$type);
                }
                closedir($dh);
            }
        }

        return $components;
    }

    /**
     * usort callback to sort plugin components
     */
    function component_sort($a, $b) {
        if ($a['name'] == $b['name']) return 0;
        return ($a['name'] < $b['name']) ? -1 : 1;
    }
}
