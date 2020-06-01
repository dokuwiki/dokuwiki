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
        return AUTH_READ; // let in check later
    }

    public function checkPreconditions() {
        parent::checkPreconditions();
    }

    public function preProcess() {
        global $INPUT;
        global $INFO;

        // retrieve admin plugin name from $_REQUEST['page']
        if(($page = $INPUT->str('page', '', true)) != '') {
            /** @var $plugin \dokuwiki\Extension\AdminPlugin */
            if($plugin = plugin_getRequestAdminPlugin()) { // FIXME this method does also permission checking
                if(!$plugin->isAccessibleByCurrentUser()) {
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
