<?php

namespace easywiki\Action;

use easywiki\Ui\Backlinks;
use easywiki\Ui;

/**
 * Class Backlink
 *
 * Shows which pages link to the current page
 *
 * @package easywiki\Action
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
