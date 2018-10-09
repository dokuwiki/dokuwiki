<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;

/**
 * Class Admin
 *
 * Action to show the admin interface or admin plugins
 *
 * @package dokuwiki\Action
 */
class Admin extends AbstractUserAction {

    /** @inheritdoc */
    public function minimumPermission() {
        global $INFO;

        if($INFO['ismanager']) {
            return AUTH_READ; // let in check later
        } else {
            return AUTH_ADMIN;
        }
    }

    public function checkPreconditions() {
        parent::checkPreconditions();

        global $INFO;
        if(!$INFO['ismanager']) {
            throw new ActionException('denied');
        }
    }

    public function preProcess() {
        global $INPUT;
        global $INFO;

        // retrieve admin plugin name from $_REQUEST['page']
        if(($page = $INPUT->str('page', '', true)) != '') {
            /** @var $plugin \DokuWiki_Admin_Plugin */
            if($plugin = plugin_getRequestAdminPlugin()) { // FIXME this method does also permission checking
                if($plugin->forAdminOnly() && !$INFO['isadmin']) {
                    throw new ActionException('denied');
                }
                $plugin->handle();
            }
        }
    }

    public function tplContent() {
        tpl_admin();
    }

}
