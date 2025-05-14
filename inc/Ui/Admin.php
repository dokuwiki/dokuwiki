<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\AdminPlugin;
use dokuwiki\Extension\PluginInterface;
use dokuwiki\Utf8\Sort;

/**
 * Class Admin
 *
 * Displays the Admin screen
 *
 * @package dokuwiki\Ui
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Håkan Sandell <hakan.sandell@home.se>
 */
class Admin extends Ui
{
    protected $forAdmins = ['usermanager', 'acl', 'extension', 'config', 'logviewer', 'styling'];
    protected $forManagers = ['revert', 'popularity'];
    /** @var array[] */
    protected $menu;

    /**
     * Display the UI element
     *
     * @return void
     */
    public function show()
    {
        $this->menu = $this->getPluginList();
        echo '<div class="ui-admin">';
        echo p_locale_xhtml('admin');

        $this->showMenu('admin');
        $this->showMenu('manager');
        $this->showSecurityCheck();
        $this->showVersion();
        $this->showMenu('other');
        echo '</div>';
    }

    /**
     * Show the given menu of available plugins
     *
     * @param string $type admin|manager|other
     */
    protected function showMenu($type)
    {
        if (!$this->menu[$type]) return;

        if ($type === 'other') {
            echo p_locale_xhtml('adminplugins');
            $class = 'admin_plugins';
        } else {
            $class = 'admin_tasks';
        }

        echo "<ul class=\"$class\">";
        foreach ($this->menu[$type] as $item) {
            $this->showMenuItem($item);
        }
        echo '</ul>';
    }

    /**
     * Display the DokuWiki version
     */
    protected function showVersion()
    {
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
     *
     * The actual check is carried out via JavaScript. See behaviour.js
     */
    protected function showSecurityCheck()
    {
        global $conf;
        if (!str_starts_with($conf['savedir'], './')) return;
        $img = DOKU_URL . $conf['savedir'] .
            '/dont-panic-if-you-see-this-in-your-logs-it-means-your-directory-permissions-are-correct.png';
        echo '<div id="security__check" data-src="' . $img . '"></div>';
    }

    /**
     * Display a single Admin menu item
     *
     * @param array $item
     */
    protected function showMenuItem($item)
    {
        global $ID;
        if (blank($item['prompt'])) return;
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
    protected function getPluginList()
    {
        global $conf;

        $pluginlist = plugin_list('admin');
        $menu = ['admin' => [], 'manager' => [], 'other' => []];

        foreach ($pluginlist as $p) {
            /** @var AdminPlugin $obj */
            if (!($obj = plugin_load('admin', $p)) instanceof PluginInterface) continue;

            // check permissions
            if (!$obj->isAccessibleByCurrentUser()) continue;

            if (in_array($p, $this->forAdmins, true)) {
                $type = 'admin';
            } elseif (in_array($p, $this->forManagers, true)) {
                $type = 'manager';
            } else {
                $type = 'other';
            }

            $menu[$type][$p] = [
                'plugin' => $p,
                'prompt' => $obj->getMenuText($conf['lang']),
                'icon' => $obj->getMenuIcon(),
                'sort' => $obj->getMenuSort()
            ];
        }

        // sort by name, then sort
        uasort($menu['admin'], [$this, 'menuSort']);
        uasort($menu['manager'], [$this, 'menuSort']);
        uasort($menu['other'], [$this, 'menuSort']);

        return $menu;
    }

    /**
     * Custom sorting for admin menu
     *
     * We sort alphabetically first, then by sort value
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function menuSort($a, $b)
    {
        $strcmp = Sort::strcmp($a['prompt'], $b['prompt']);
        if ($strcmp != 0) return $strcmp;
        return $a['sort'] <=> $b['sort'];
    }
}
