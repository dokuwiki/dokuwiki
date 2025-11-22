<?php

namespace easywiki\Action;

use easywiki\Ui\PageDraft;
use easywiki\Action\Exception\ActionException;
use easywiki\Ui;

/**
 * Class Draft
 *
 * Screen to see and recover a draft
 *
 * @package easywiki\Action
 * @fixme combine with Recover?
 */
class Draft extends AbstractAction
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
    public function checkPreconditions()
    {
        parent::checkPreconditions();
        global $INFO;
        if (!isset($INFO['draft']) || !file_exists($INFO['draft'])) throw new ActionException('edit');
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new PageDraft())->show();
    }
}
