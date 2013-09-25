<?php

/**
 * Handler for action admin
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Admin extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string (the action name)
     */
    public function action() {
        return "admin";
    }

    /**
     * The Doku_Action interface to specify the required permission 
     * for actions subscribe and unsubscribe
     * @return string the required permission
     * 
     * @global $INFO
     */
    public function permission_required() {
        global $INFO;
        // if the manager has the needed permissions for a certain admin
        // action is checked later
        if ($INFO['ismanager']) return AUTH_READ;
        return AUTH_ADMIN;
    }

    /**
     * The Doku_Action interface to handle action unsubscribe
     * 
     * @global $INPUT
     * @global $INFO
     * @global $conf
     * @return string the next action
     */
    public function handle() {
        global $INPUT;
        global $INFO;
        global $conf;
        //disable all acl related commands if ACL is disabled
        if (!$conf['useacl']) {
            msg('Command unavailable: '.htmlspecialchars($act),-1);
            return 'show';
        }
        // retrieve admin plugin name from $_REQUEST['page']
        if (($page = $INPUT->str('page', '', true)) != '') {
            $pluginlist = plugin_list('admin');
            if (in_array($page, $pluginlist)) {
                // attempt to load the plugin
                if ($plugin =& plugin_load('admin',$page) !== null){
                    /** @var DokuWiki_Admin_Plugin $plugin */
                    if($plugin->forAdminOnly() && !$INFO['isadmin']){
                        // a manager tried to load a plugin that's for admins only
                        $INPUT->remove('page');
                        msg('For admins only',-1);
                    }else{
                        $plugin->handle();
                    }
                }
            }
        }
    }

    /**
     * List available Administration Tasks
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Håkan Sandell <hakan.sandell@home.se>
     */
    function html_admin(){
        global $ID;
        global $INFO;
        global $conf;
        /** @var auth_basic $auth */
        global $auth;

        // build menu of admin functions from the plugins that handle them
        $pluginlist = plugin_list('admin');
        $menu = array();
        foreach ($pluginlist as $p) {
            /** @var DokuWiki_Admin_Plugin $obj */
            if($obj =& plugin_load('admin',$p) === null) continue;

            // check permissions
            if($obj->forAdminOnly() && !$INFO['isadmin']) continue;

            $menu[$p] = array('plugin' => $p,
                    'prompt' => $obj->getMenuText($conf['lang']),
                    'sort' => $obj->getMenuSort()
                    );
        }

        // data security check
        // simple check if the 'savedir' is relative and accessible when appended to DOKU_URL
        // it verifies either:
        //   'savedir' has been moved elsewhere, or
        //   has protection to prevent the webserver serving files from it
        if (substr($conf['savedir'],0,2) == './'){
            echo '<a style="border:none; float:right;"
                    href="http://www.dokuwiki.org/security#web_access_security">
                    <img src="'.DOKU_URL.$conf['savedir'].'/security.png" alt="Your data directory seems to be protected properly."
                    onerror="this.parentNode.style.display=\'none\'" /></a>';
        }

        print p_locale_xhtml('admin');

        // Admin Tasks
        if($INFO['isadmin']){
            ptln('<ul class="admin_tasks">');

            if($menu['usermanager'] && $auth && $auth->canDo('getUsers')){
                ptln('  <li class="admin_usermanager"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'usermanager')).'">'.
                        $menu['usermanager']['prompt'].'</a></div></li>');
            }
            unset($menu['usermanager']);

            if($menu['acl']){
                ptln('  <li class="admin_acl"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'acl')).'">'.
                        $menu['acl']['prompt'].'</a></div></li>');
            }
            unset($menu['acl']);

            if($menu['plugin']){
                ptln('  <li class="admin_plugin"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'plugin')).'">'.
                        $menu['plugin']['prompt'].'</a></div></li>');
            }
            unset($menu['plugin']);

            if($menu['config']){
                ptln('  <li class="admin_config"><div class="li">'.
                        '<a href="'.wl($ID, array('do' => 'admin','page' => 'config')).'">'.
                        $menu['config']['prompt'].'</a></div></li>');
            }
            unset($menu['config']);
        }
        ptln('</ul>');

        // Manager Tasks
        ptln('<ul class="admin_tasks">');

        if($menu['revert']){
            ptln('  <li class="admin_revert"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'revert')).'">'.
                    $menu['revert']['prompt'].'</a></div></li>');
        }
        unset($menu['revert']);

        if($menu['popularity']){
            ptln('  <li class="admin_popularity"><div class="li">'.
                    '<a href="'.wl($ID, array('do' => 'admin','page' => 'popularity')).'">'.
                    $menu['popularity']['prompt'].'</a></div></li>');
        }
        unset($menu['popularity']);

        // print DokuWiki version:
        ptln('</ul>');
        echo '<div id="admin__version">';
        echo getVersion();
        echo '</div>';

        // print the rest as sorted list
        if(count($menu)){
            usort($menu, 'p_sort_modes');
            // output the menu
            ptln('<div class="clearer"></div>');
            print p_locale_xhtml('adminplugins');
            ptln('<ul>');
            foreach ($menu as $item) {
                if (!$item['prompt']) continue;
                ptln('  <li><div class="li"><a href="'.wl($ID, 'do=admin&amp;page='.$item['plugin']).'">'.$item['prompt'].'</a></div></li>');
            }
            ptln('</ul>');
        }
    }

    /**
     * Doku_Action interface to display the admin page
     * Was tpl_admin() by 
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global $INFO
     * @global $TOC
     * @global $INPUT
     */
    public function html() {
        global $INFO;
        global $TOC;
        global $INPUT;

        $plugin = null;
        $class  = $INPUT->str('page');
        if(!empty($class)) {
            $pluginlist = plugin_list('admin');

            if(in_array($class, $pluginlist)) {
                // attempt to load the plugin
                /** @var $plugin DokuWiki_Admin_Plugin */
                $plugin =& plugin_load('admin', $class);
            }
        }

        if($plugin !== null) {
            if(!is_array($TOC)) $TOC = $plugin->getTOC(); //if TOC wasn't requested yet
            if($INFO['prependTOC']) tpl_toc();
            $plugin->html();
        } else {
            $this->html_admin();
        }
        return true;
    }
}