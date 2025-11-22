<?php

namespace easywiki\Action;

use easywiki\Ui\PageRevisions;
use easywiki\Ui;

/**
 * Class Revisions
 *
 * Show the list of old revisions of the current page
 *
 * @package easywiki\Action
 */
class Revisions extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        global $INFO, $INPUT;
        (new PageRevisions($INFO['id']))->show($INPUT->int('first', -1));
    }
}
