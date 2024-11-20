<?php

namespace dokuwiki\plugin\extension;

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

        $plugins = (new Local())->getTemplates();

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
    public function tabTemplates() {
        $html = '<div class="panelHeader">';
        $html .= $this->helper->locale_xhtml('intro_templates');
        $html .= '</div>';

        $templates = (new Local())->getTemplates();

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

}
