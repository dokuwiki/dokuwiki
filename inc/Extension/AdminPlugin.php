<?php

namespace dokuwiki\Extension;

/**
 * Admin Plugin Prototype
 *
 * Implements an admin interface in a plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
abstract class AdminPlugin extends Plugin
{

    /**
     * Return the text that is displayed at the main admin menu
     * (Default localized language string 'menu' is returned, override this function for setting another name)
     *
     * @param string $language language code
     * @return string menu string
     */
    public function getMenuText($language)
    {
        $menutext = $this->getLang('menu');
        if (!$menutext) {
            $info = $this->getInfo();
            $menutext = $info['name'] . ' ...';
        }
        return $menutext;
    }

    /**
     * Return the path to the icon being displayed in the main admin menu.
     * By default it tries to find an 'admin.svg' file in the plugin directory.
     * (Override this function for setting another image)
     *
     * Important: you have to return a single path, monochrome SVG icon! It has to be
     * under 2 Kilobytes!
     *
     * We recommend icons from https://materialdesignicons.com/ or to use a matching
     * style.
     *
     * @return string full path to the icon file
     */
    public function getMenuIcon()
    {
        $plugin = $this->getPluginName();
        return DOKU_PLUGIN . $plugin . '/admin.svg';
    }

    /**
     * Determine position in list in admin window
     * Lower values are sorted up
     *
     * @return int
     */
    public function getMenuSort()
    {
        return 1000;
    }

    /**
     * Carry out required processing
     */
    public function handle()
    {
        // some plugins might not need this
    }

    /**
     * Output html of the admin page
     */
    abstract public function html();

    /**
     * Checks if access should be granted to this admin plugin
     *
     * @return bool true if the current user may access this admin plugin
     */
    public function isAccessibleByCurrentUser() {
        $data = [];
        $data['instance'] = $this;
        $data['hasAccess'] = false;

        $event = new Event('ADMINPLUGIN_ACCESS_CHECK', $data);
        if($event->advise_before()) {
            if ($this->forAdminOnly()) {
                $data['hasAccess'] = auth_isadmin();
            } else {
                $data['hasAccess'] = auth_ismanager();
            }
        }
        $event->advise_after();

        return $data['hasAccess'];
    }

    /**
     * Return true for access only by admins (config:superuser) or false if managers are allowed as well
     *
     * @return bool
     */
    public function forAdminOnly()
    {
        return true;
    }

    /**
     * Return array with ToC items. Items can be created with the html_mktocitem()
     *
     * @see html_mktocitem()
     * @see tpl_toc()
     *
     * @return array
     */
    public function getTOC()
    {
        return array();
    }

}

