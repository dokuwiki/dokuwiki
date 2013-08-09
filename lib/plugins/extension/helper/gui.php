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

    protected $tabs = array('plugins', 'templates', 'search', 'install');

    /** @var string the extension that should have an open info window FIXME currently broken*/
    protected $infofor = '';

    /**
     * display the plugin tab
     */
    public function tabPlugins() {
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;

        echo $this->locale_xhtml('intro_plugins');

        $pluginlist = $plugin_controller->getList('', true);
        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');
        $list->start_form();
        foreach($pluginlist as $name) {
            $extension->setExtension($name);
            $list->add_row($extension, $name == $this->infoFor);
        }
        $list->end_form();
        $list->render();
    }

    /**
     * Display the template tab
     */
    public function tabTemplates() {
        echo $this->locale_xhtml('intro_templates');

        // FIXME do we have a real way?
        $tpllist = glob(DOKU_INC.'lib/tpl/*', GLOB_ONLYDIR);
        $tpllist = array_map('basename', $tpllist);

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');
        $list->start_form();
        foreach($tpllist as $name) {
            $extension->setExtension("template:$name");
            $list->add_row($extension, $name == $this->infoFor);
        }
        $list->end_form();
        $list->render();
    }

    /**
     * Display the search tab
     */
    public function tabSearch() {
        global $INPUT;
        echo $this->locale_xhtml('intro_search');

        $form = new Doku_Form(array('action' => $this->tabURL('', array(), '&')));
        $form->addElement(form_makeTextField('q', $INPUT->str('q'), 'Search'));
        $form->addElement(form_makeButton('submit', '', 'Search'));
        $form->printForm();

        if(!$INPUT->bool('q')) return;


        /* @var helper_plugin_extension_repository $repository FIXME should we use some gloabl instance? */
        $repository = $this->loadHelper('extension_repository');
        $result = $repository->search($INPUT->str('q'));

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');
        $list->start_form();
        foreach($result as $name) {
            $extension->setExtension($name);
            $list->add_row($extension, $name == $this->infoFor);
        }
        $list->end_form();
        $list->render();

    }

    /**
     * Display the template tab
     */
    public function tabInstall() {
        echo $this->locale_xhtml('intro_install');
    }

    /**
     * Print the tab navigation
     *
     * @fixme style active one
     */
    public function tabNavigation() {
        echo '<ul class="tabs">';
        foreach($this->tabs as $tab) {
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
     * @param string $sep    seperator to build the URL
     * @return string
     */
    public function tabURL($tab = '', $params = array(), $sep = '&amp;') {
        global $ID;

        if(!$tab) $tab = $this->currentTab();
        $defaults = array(
            'do'   => 'admin',
            'page' => 'extension',
            'tab'  => $tab,
        );
        return wl($ID, array_merge($defaults, $params), false, $sep);
    }

}
