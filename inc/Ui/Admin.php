<?php
namespace dokuwiki\Ui;

/**
 * Class Admin
 *
 * Displays the Admin screen
 *
 * @package dokuwiki\Ui
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Håkan Sandell <hakan.sandell@home.se>
 */
class Admin extends Ui {

    protected $menu;

    /**
     * Display the UI element
     *
     * @return void
     */
    public function show() {
        $this->menu = $this->getPluginList();
        $this->showSecurityCheck();
        echo '<div class="ui-admin">';
        echo p_locale_xhtml('admin');
        $this->showAdminMenu();
        $this->showManagerMenu();
        $this->showVersion();
        $this->showPluginMenu();
        echo '</div>';
    }

    /**
     * Display the standard admin tasks
     */
    protected function showAdminMenu() {
        /** @var \DokuWiki_Auth_Plugin $auth */
        global $auth;
        global $INFO;

        if(!$INFO['isadmin']) return;

        // user manager only if the auth backend supports it
        if(!$auth || !$auth->canDo('getUsers') ) {
            if(isset($this->menu['usermanager'])) unset($this->menu['usermanager']);
        }

        echo '<ul class="admin_tasks">';
        foreach(array('usermanager','acl', 'extension', 'config', 'styling') as $plugin) {
            if(!isset($this->menu[$plugin])) continue;
            $this->showMenuItem($this->menu[$plugin]);
            unset($this->menu[$plugin]);
        }
        echo '</ul>';
    }

    /**
     * Display the standard manager tasks
     */
    protected function showManagerMenu() {
        echo '<ul class="admin_tasks">';
        foreach(array('revert','popularity') as $plugin) {
            if(!isset($this->menu[$plugin])) continue;
            $this->showMenuItem($this->menu[$plugin]);
            unset($this->menu[$plugin]);
        }
        echo '</ul>';
    }

    /**
     * Display all the remaining plugins
     */
    protected function showPluginMenu() {
        if(!count($this->menu)) return;
        echo p_locale_xhtml('adminplugins');
        echo '<ul class="admin_plugins">';
        foreach ($this->menu as $item) {
            $this->showMenuItem($item);
        }
        echo '</ul>';
    }

    /**
     * Display the DokuWiki version
     */
    protected function showVersion() {
        echo '<div id="admin__version">';
        echo getVersion();
        echo '</div>';
    }

    /**
     * data security check
     *
     * simple check if the 'savedir' is relative and accessible when appended to DOKU_URL
     *
     * it verifies either:
     *   'savedir' has been moved elsewhere, or
     *   has protection to prevent the webserver serving files from it
     */
    protected function showSecurityCheck() {
        if(substr($GLOBALS['conf']['savedir'], 0, 2) !== './') return;

        $url = DOKU_URL.$GLOBALS['conf']['savedir'].'/security.png';
        // Fail is 'success' because we check if there is no access to file in the data folder
        echo <<<CHK
<button style="padding: 0.2rem; float:right" onclick="var btn = this; jQuery.ajax('$url')
.fail(function(xhr) {
 btn.style['background'] = 'green';
 btn.style['color'] = 'white';
 btn.innerHTML = '<b>'+xhr.status+'</b>: Your data directory seems to be protected properly.'
}).success(function(data,result,xhr) {
    console.log(xhr)
 btn.style['background'] = 'red';
 btn.style['color'] = 'white';
 btn.innerHTML = '<b>'+xhr.status+'</b>: It seems your data directory is <b>NOT</b> properly secured!<br>Please read<br><a target=\'_blank\' href=\'https://dokuwiki.org/security#web_access_security\'>https://dokuwiki.org/security</a>';
});" >security check</button>
CHK;
    }

    /**
     * Display a single Admin menu item
     *
     * @param array $item
     */
    protected function showMenuItem($item) {
        global $ID;
        if(blank($item['prompt'])) return;
        echo '<li><div class="li">';
        echo '<a href="' . wl($ID, 'do=admin&amp;page=' . $item['plugin']) . '">';
        echo '<span class="icon">';
        echo inlineSVG($item['icon']);
        echo '</span>';
        echo '<span class="prompt">';
        echo $item['prompt'];
        echo '</span>';
        echo '</a>';
        echo '</div></li>';
    }

    /**
     * Build  list of admin functions from the plugins that handle them
     *
     * Checks the current permissions to decide on manager or admin plugins
     *
     * @return array list of plugins with their properties
     */
    protected function getPluginList() {
        global $INFO;
        global $conf;

        $pluginlist = plugin_list('admin');
        $menu = array();
        foreach($pluginlist as $p) {
            /** @var \DokuWiki_Admin_Plugin $obj */
            if(($obj = plugin_load('admin', $p)) === null) continue;

            // check permissions
            if($obj->forAdminOnly() && !$INFO['isadmin']) continue;

            $menu[$p] = array(
                'plugin' => $p,
                'prompt' => $obj->getMenuText($conf['lang']),
                'icon' => $obj->getMenuIcon(),
                'sort' => $obj->getMenuSort(),
            );
        }

        // sort by name, then sort
        uasort(
            $menu,
            function ($a, $b) {
                $strcmp = strcasecmp($a['prompt'], $b['prompt']);
                if($strcmp != 0) return $strcmp;
                if($a['sort'] == $b['sort']) return 0;
                return ($a['sort'] < $b['sort']) ? -1 : 1;
            }
        );

        return $menu;
    }

}
