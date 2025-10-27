<?php

namespace dokuwiki\Action;

use dokuwiki\Ui\Backlinks;
use dokuwiki\Ui;

/**
 * Class Backlink
 *
 * Shows which pages link to the current page
 *
 * @package dokuwiki\Action
 */
class Backlink extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new Backlinks())->show();
    }
}
