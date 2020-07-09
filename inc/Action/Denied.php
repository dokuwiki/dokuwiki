<?php

namespace dokuwiki\Action;

use dokuwiki\Ui;

/**
 * Class Denied
 *
 * Show the access denied screen
 *
 * @package dokuwiki\Action
 */
class Denied extends AbstractAclAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    public function tplContent()
    {
        (new Ui\Denied)->show();
    }

}
