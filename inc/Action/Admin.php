<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:33 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;

class Admin extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
        global $INFO;

        if($INFO['ismanager']) {
            return AUTH_READ; // let in check later
        } else {
            return AUTH_ADMIN;
        }
    }

    public function checkPermissions() {
        parent::checkPermissions();

        global $INFO;
        if(!$INFO['ismanager']) {
            throw new ActionException('denied');
        }
    }

    public function preProcess() {
        global $INPUT;
        global $INFO;

        // retrieve admin plugin name from $_REQUEST['page']
        if (($page = $INPUT->str('page', '', true)) != '') {
            /** @var $plugin \DokuWiki_Admin_Plugin */
            if ($plugin = plugin_getRequestAdminPlugin()){ // FIXME this method does also permission checking
                if($plugin->forAdminOnly() && !$INFO['isadmin'] ) {
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
