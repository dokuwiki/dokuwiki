<?php

namespace dokuwiki\Action;

use dokuwiki\Ui;

/**
 * Class Conflict
 *
 * Show the conflict resolution screen
 *
 * @package dokuwiki\Action
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

    public function tplContent()
    {
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        (new Ui\Conflict)->show(con($PRE, $TEXT, $SUF), $SUM);
        html_diff(con($PRE, $TEXT, $SUF), false);
    }

}
