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

    public function tabPlugins()
    {
        $html = '<div class="panelHeader">';
        $html .= $this->helper->locale_xhtml('intro_plugins');
        $html .= '</div>';

        $pluginlist = plugin_list('', true);

        $html .= '<div id="extension__list">';
        // FIXME wrap in form
        foreach ($pluginlist as $name) {
            $ext = Extension::createFromId($name);
            $gui = new GuiExtension($ext);
            $html .= $gui->render();
        }
        $html .= '</div>';

        return $html;
    }
}
