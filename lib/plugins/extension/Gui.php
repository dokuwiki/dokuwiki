<?php

namespace dokuwiki\plugin\extension;

class Gui
{
    protected $tabs = ['plugins', 'templates', 'search', 'install'];

    protected $helper;


    public function __construct()
    {
        $this->helper = plugin_load('helper', 'extension');
    }


    public function getLang($msg)
    {
        return $this->helper->getLang($msg);
    }

    /**
     * Return the currently selected tab
     *
     * @return string
     */
    public function currentTab()
    {
        global $INPUT;

        $tab = $INPUT->str('tab', 'plugins', true);
        if (!in_array($tab, $this->tabs)) $tab = 'plugins';
        return $tab;
    }

    /**
     * Create an URL inside the extension manager
     *
     * @param string $tab tab to load, empty for current tab
     * @param array $params associative array of parameter to set
     * @param string $sep seperator to build the URL
     * @param bool $absolute create absolute URLs?
     * @return string
     */
    public function tabURL($tab = '', $params = [], $sep = '&', $absolute = false)
    {
        global $ID;
        global $INPUT;

        if (!$tab) $tab = $this->currentTab();
        $defaults = [
            'do' => 'admin',
            'page' => 'extension',
            'tab' => $tab
        ];
        if ($tab == 'search') $defaults['q'] = $INPUT->str('q');

        return wl($ID, array_merge($defaults, $params), $absolute, $sep);
    }
}
