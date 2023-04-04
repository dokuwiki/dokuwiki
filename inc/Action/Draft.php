<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Ui;

/**
 * Class Draft
 *
 * Screen to see and recover a draft
 *
 * @package dokuwiki\Action
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
        if (!file_exists($INFO['draft'])) throw new ActionException('edit');
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new Ui\PageDraft)->show();
    }

}
