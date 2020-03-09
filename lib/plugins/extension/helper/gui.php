<?php
/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\Form\Form;

/**
 * Class helper_plugin_extension_list takes care of the overall GUI
 */
class helper_plugin_extension_gui extends DokuWiki_Plugin
{
    protected $tabs = array('plugins', 'templates', 'search', 'install');

    /** @var string the extension that should have an open info window FIXME currently broken */
    protected $infoFor = '';

    /**
     * Constructor
     *
     * initializes requested info window
     */
    public function __construct()
    {
        global $INPUT;
        $this->infoFor = $INPUT->str('info');
    }

    /**
     * display the plugin tab
     */
    public function tabPlugins()
    {
        echo '<div class="panelHeader">';
        echo $this->locale_xhtml('intro_plugins');
        echo '</div>';

        $pluginlist = plugin_list('', true);
        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');

        $form = new Form([
                'action' => $this->tabURL('', [], '&'),
                'id'  => 'extension__list',
        ]);
        $list->startForm();
        foreach ($pluginlist as $name) {
            $extension->setExtension($name);
            $list->addRow($extension, $extension->getID() == $this->infoFor);
        }
        $list->endForm();
        $form->addHTML($list->render(true));
        echo $form->toHTML();
    }

    /**
     * Display the template tab
     */
    public function tabTemplates()
    {
        echo '<div class="panelHeader">';
        echo $this->locale_xhtml('intro_templates');
        echo '</div>';

        // FIXME do we have a real way?
        $tpllist = glob(DOKU_INC.'lib/tpl/*', GLOB_ONLYDIR);
        $tpllist = array_map('basename', $tpllist);
        sort($tpllist);

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');

        $form = new Form([
                'action' => $this->tabURL('', [], '&'),
                'id'  => 'extension__list',
        ]);
        $list->startForm();
        foreach ($tpllist as $name) {
            $extension->setExtension("template:$name");
            $list->addRow($extension, $extension->getID() == $this->infoFor);
        }
        $list->endForm();
        $form->addHTML($list->render(true));
        echo $form->toHTML();
    }

    /**
     * Display the search tab
     */
    public function tabSearch()
    {
        global $INPUT;
        echo '<div class="panelHeader">';
        echo $this->locale_xhtml('intro_search');
        echo '</div>';

        $form = new Form([
                'action' => $this->tabURL('', [], '&'),
                'class'  => 'search',
        ]);
        $form->addTagOpen('div')->addClass('no');
        $form->addTextInput('q', $this->getLang('search_for'))
            ->addClass('edit')
            ->val($INPUT->str('q'));
        $form->addButton('submit', $this->getLang('search'))
            ->attrs(['type' => 'submit', 'title' => $this->getLang('search')]);
        $form->addTagClose('div');
        echo $form->toHTML();

        if (!$INPUT->bool('q')) return;

        /* @var helper_plugin_extension_repository $repository FIXME should we use some gloabl instance? */
        $repository = $this->loadHelper('extension_repository');
        $result     = $repository->search($INPUT->str('q'));

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');

        $form = new Form([
                'action' => $this->tabURL('', [], '&'),
                'id'  => 'extension__list',
        ]);
        $list->startForm();
        if ($result) {
            foreach ($result as $name) {
                $extension->setExtension($name);
                $list->addRow($extension, $extension->getID() == $this->infoFor);
            }
        } else {
            $list->nothingFound();
        }
        $list->endForm();
        $form->addHTML($list->render(true));
        echo $form->toHTML();
    }

    /**
     * Display the template tab
     */
    public function tabInstall()
    {
        global $lang;
        echo '<div class="panelHeader">';
        echo $this->locale_xhtml('intro_install');
        echo '</div>';

        $form = new Form([
                'action' => $this->tabURL('', [], '&'),
                'enctype' => 'multipart/form-data',
                'class'  => 'install',
        ]);
        $form->addTagOpen('div')->addClass('no');
        $form->addTextInput('installurl', $this->getLang('install_url'))
            ->addClass('block')
            ->attrs(['type' => 'url']);
        $form->addTag('br');
        $form->addTextInput('installfile', $this->getLang('install_upload'))
            ->addClass('block')
            ->attrs(['type' => 'file']);
        $form->addTag('br');
        $form->addCheckbox('overwrite', $lang['js']['media_overwrt'])
            ->addClass('block');
        $form->addTag('br');
        $form->addButton('', $this->getLang('btn_install'))
            ->attrs(['type' => 'submit', 'title' => $this->getLang('btn_install')]);
        $form->addTagClose('div');
        echo $form->toHTML();
    }

    /**
     * Print the tab navigation
     *
     * @fixme style active one
     */
    public function tabNavigation()
    {
        echo '<ul class="tabs">';
        foreach ($this->tabs as $tab) {
            $url = $this->tabURL($tab);
            if ($this->currentTab() == $tab) {
                $class = ' active';
            } else {
                $class = '';
            }
            echo '<li class="'.$tab.$class.'"><a href="'.$url.'">'.$this->getLang('tab_'.$tab).'</a></li>';
        }
        echo '</ul>';
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
     * @param string $tab      tab to load, empty for current tab
     * @param array  $params   associative array of parameter to set
     * @param string $sep      seperator to build the URL
     * @param bool   $absolute create absolute URLs?
     * @return string
     */
    public function tabURL($tab = '', $params = [], $sep = '&', $absolute = false)
    {
        global $ID;
        global $INPUT;

        if (!$tab) $tab = $this->currentTab();
        $defaults = array(
            'do'   => 'admin',
            'page' => 'extension',
            'tab'  => $tab,
        );
        if ($tab == 'search') $defaults['q'] = $INPUT->str('q');

        return wl($ID, array_merge($defaults, $params), $absolute, $sep);
    }
}
