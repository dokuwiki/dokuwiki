<?php

namespace dokuwiki\plugin\extension;

use dokuwiki\Form\Form;

class GuiAdmin extends Gui
{
    public function render()
    {
        $html = '<div id="extension__manager">';

        $html .= $this->tabNavigation();

        switch ($this->currentTab()) {
            case 'search':
                $html .= $this->tabSearch();
                break;
            case 'templates':
                $html .= $this->tabTemplates();
                break;
            case 'install':
                $html .= $this->tabInstall();
                break;
            case 'plugins':
            default:
                $html .= $this->tabPlugins();
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Print the tab navigation
     *
     */
    public function tabNavigation()
    {
        $html = '<ul class="tabs">';
        foreach ($this->tabs as $tab) {
            $url = $this->tabURL($tab);
            if ($this->currentTab() == $tab) {
                $class = ' active';
            } else {
                $class = '';
            }
            $html .= '<li class="' . $tab . $class . '"><a href="' . $url . '">' .
                $this->getLang('tab_' . $tab) . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Return the HTML for the list of installed plugins
     *
     * @return string
     */
    public function tabPlugins()
    {
        $html = '<div class="panelHeader">';
        $html .= $this->helper->locale_xhtml('intro_plugins');
        $html .= '</div>';

        $plugins = (new Local())->getPlugins();
        try {
            // initialize remote data in one go
            Repository::getInstance()->initExtensions(array_keys($plugins));
        } catch (Exception $e) {
            msg($e->getMessage(), -1); // this should not happen
        }

        $html .= '<div id="extension__list">';
        $html .= '<form action="' . $this->tabURL('plugins') . '" method="post">';
        $html .= '<input type="hidden" name="overwrite" value="1">';
        $html .= formSecurityToken(false);
        foreach ($plugins as $ext) {
            $gui = new GuiExtension($ext);
            $html .= $gui->render();
        }
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Return the HTML for the list of installed templates
     *
     * @return string
     */
    public function tabTemplates()
    {
        $html = '<div class="panelHeader">';
        $html .= $this->helper->locale_xhtml('intro_templates');
        $html .= '</div>';

        $templates = (new Local())->getTemplates();
        try {
            // initialize remote data in one go
            Repository::getInstance()->initExtensions(array_keys($templates));
        } catch (Exception $e) {
            msg($e->getMessage(), -1); // this should not happen
        }

        $html .= '<div id="extension__list">';
        $html .= '<form action="' . $this->tabURL('templates') . '" method="post">';
        $html .= '<input type="hidden" name="overwrite" value="1">';
        $html .= formSecurityToken(false);
        foreach ($templates as $ext) {
            $gui = new GuiExtension($ext);
            $html .= $gui->render();
        }
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Return the HTML for the search tab
     *
     * @return string
     */
    public function tabSearch()
    {
        global $INPUT;

        $html = '<div class="panelHeader">';
        $html .= $this->helper->locale_xhtml('intro_search');
        $html .= '</div>';

        $form = new Form([
            'action' => $this->tabURL('search'),
            'class' => 'search',
        ]);
        $form->addTagOpen('div')->addClass('no');
        $form->addTextInput('q', $this->getLang('search_for'))
            ->addClass('edit')
            ->val($INPUT->str('q'));
        $form->addButton('submit', $this->getLang('search'))
            ->attrs(['type' => 'submit', 'title' => $this->getLang('search')]);
        $form->addTagClose('div');
        $html .= $form->toHTML();

        if ($INPUT->str('q')) $html .= $this->searchResults($INPUT->str('q'));

        return $html;
    }

    /**
     * Return the HTML for the install tab
     *
     * @return string
     */
    public function tabInstall()
    {
        global $lang;

        $html = '<div class="panelHeader">';
        $html .= $this->helper->locale_xhtml('intro_install');
        $html .= '</div>';

        $form = new Form([
            'action' => $this->tabURL('install'),
            'enctype' => 'multipart/form-data',
            'class' => 'install',
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
        $html .= $form->toHTML();

        return $html;
    }

    /**
     * Execute the given search query and return the results
     *
     * @param string $q the query
     * @return string
     */
    protected function searchResults($q)
    {
        $repo = Repository::getInstance();

        $html = '<div id="extension__list">';
        $html .= '<form action="' . $this->tabURL('search') . '" method="post">';
        $html .= formSecurityToken(false);

        try {
            $extensions = $repo->searchExtensions($q);
            $html .= '<div id="extension__results">';
            foreach ($extensions as $ext) {
                $gui = new GuiExtension($ext);
                $html .= $gui->render();
            }
            $html .= '</div>';
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }
}
