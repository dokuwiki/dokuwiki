<?php
/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_extension_list takes care of the overall GUI
 */
class helper_plugin_extension_gui extends DokuWiki_Plugin {

    protected $tabs = array('plugins', 'templates', 'search');

    /**
     * Print the tab navigation
     *
     * @fixme style active one
     */
    public function tabNavigation() {
        echo '<ul class="tabs">';
        foreach(array('plugins', 'templates', 'search') as $tab) {
            $url = $this->tabURL($tab);
            if($this->currentTab() == $tab) {
                $class = 'class="active"';
            } else {
                $class = '';
            }
            echo '<li '.$class.'><a href="'.$url.'">'.$this->getLang('tab_'.$tab).'</a></li>';
        }
        echo '</ul>';
    }

    /**
     * Return the currently selected tab
     *
     * @return string
     */
    public function currentTab() {
        global $INPUT;

        $tab = $INPUT->str('tab', 'plugins', true);
        if(!in_array($tab, $this->tabs)) $tab = 'plugins';
        return $tab;
    }

    /**
     * Create an URL inside the extension manager
     *
     * @param string $tab    tab to load, empty for current tab
     * @param array  $params associative array of parameter to set
     *
     * @return string
     */
    public function tabURL($tab = '', $params = array()) {
        global $ID;

        if(!$tab) $tab = $this->currentTab();
        $defaults = array(
            'do'   => 'admin',
            'page' => 'extension',
            'tab'  => $tab,
        );
        return wl($ID, array_merge($defaults, $params));
    }

}
