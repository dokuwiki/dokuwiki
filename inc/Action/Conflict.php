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

    /** @inheritdoc */
    public function tplContent()
    {
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        $this->showBanner();
        $text = con($PRE, $TEXT, $SUF);
        (new Ui\ConflictForm($text, $SUM))->show();
        (new Ui\Diff($text, false))->show();
    }

    /**
     * Show warning on conflict detection
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    protected function showBanner()
    {
        // print intro
        print p_locale_xhtml('conflict');
    }


}
