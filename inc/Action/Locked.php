<?php

namespace dokuwiki\Action;

use dokuwiki\Ui\Editor;

/**
 * Class Locked
 *
 * Show a locked screen when a page is locked
 *
 * @package dokuwiki\Action
 */
class Locked extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        $this->showBanner();
        (new Editor())->show();
    }

    /**
     * Display error on locked pages
     *
     * @return void
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function showBanner()
    {
        global $ID;
        global $conf;
        global $lang;
        global $INFO;

        $locktime = filemtime(wikiLockFN($ID));
        $expire = dformat($locktime + $conf['locktime']);
        $min = round(($conf['locktime'] - (time() - $locktime)) / 60);

        // print intro
        echo p_locale_xhtml('locked');

        echo '<ul>';
        echo '<li><div class="li"><strong>' . $lang['lockedby'] . '</strong> ' .
            editorinfo($INFO['locked']) . '</div></li>';
        echo '<li><div class="li"><strong>' . $lang['lockexpire'] . '</strong> ' .
            $expire . ' (' . $min . ' min)</div></li>';
        echo '</ul>' . DOKU_LF;
    }
}
