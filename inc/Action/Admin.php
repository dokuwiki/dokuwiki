<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Extension\AdminPlugin;

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

    /** @inheritDoc */
    public function preProcess() {
        global $INPUT;

        // retrieve admin plugin name from $_REQUEST['page']
        if($INPUT->str('page', '', true) != '') {
            /** @var AdminPlugin $plugin */
            if($plugin = plugin_getRequestAdminPlugin()) { // FIXME this method does also permission checking
                if(!$plugin->isAccessibleByCurrentUser()) {
                    throw new ActionException('denied');
                }
                $plugin->handle();
            }
        }
    }

    /** @inheritDoc */
    public function tplContent() {
        tpl_admin();
    }
}
