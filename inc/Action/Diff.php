<?php

namespace dokuwiki\Action;

use dokuwiki\Ui;

/**
 * Class Diff
 *
 * Show the differences between two revisions
 *
 * @package dokuwiki\Action
 */
class Diff extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $INPUT;

        // store the selected diff type in cookie
        $diffType = $INPUT->str('difftype');
        if (!empty($diffType)) {
            set_doku_pref('difftype', $diffType);
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new Ui\Diff())->show();
    }

}
