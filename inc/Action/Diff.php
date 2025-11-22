<?php

namespace easywiki\Action;

use easywiki\Ui\PageDiff;
use easywiki\Ui;

/**
 * Class Diff
 *
 * Show the differences between two revisions
 *
 * @package easywiki\Action
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
        $difftype = $INPUT->str('difftype');
        if (!empty($difftype)) {
            set_doku_pref('difftype', $difftype);
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        global $INFO;
        (new PageDiff($INFO['id']))->preference('showIntro', true)->show();
    }
}
