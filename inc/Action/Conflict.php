<?php

namespace easywiki\Action;

use easywiki\Ui\PageConflict;
use easywiki\Ui;

/**
 * Class Conflict
 *
 * Show the conflict resolution screen
 *
 * @package easywiki\Action
 */
class Conflict extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        global $INFO;
        if ($INFO['exists']) {
            return AUTH_EDIT;
        } else {
            return AUTH_CREATE;
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        $text = con($PRE, $TEXT, $SUF);
        (new PageConflict($text, $SUM))->show();
    }
}
