<?php

namespace easywiki\Action;

use easywiki\Ui\Editor;
use easywiki\Ui;

/**
 * Class Source
 *
 * Show the source of a page
 *
 * @package easywiki\Action
 */
class Source extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $TEXT;
        global $INFO;
        global $ID;
        global $REV;

        if ($INFO['exists']) {
            $TEXT = rawWiki($ID, $REV);
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new Editor())->show();
    }
}
