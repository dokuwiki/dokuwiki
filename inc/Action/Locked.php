<?php

namespace dokuwiki\Action;

use dokuwiki\Ui;

/**
 * Class Locked
 *
 * Show a locked screen when a page is locked
 *
 * @package dokuwiki\Action
 */
class Locked extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new Ui\Locked)->show();
        (new Ui\Editor)->show();
    }

}
