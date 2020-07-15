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

        $this->showBanner();
        (new Ui\ConflictForm)->show(con($PRE, $TEXT, $SUF), $SUM);
        (new Ui\Diff)->show(con($PRE, $TEXT, $SUF), false);
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
