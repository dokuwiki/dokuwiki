<?php

namespace dokuwiki\Action;

use dokuwiki\Ui;

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
        (new Ui\Editor)->show();
    }

    /**
     * Display error on locked pages
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function showBanner()
    {
        global $ID;
        global $conf;
        global $lang;
        global $INFO;

        $locktime = filemtime(wikiLockFN($ID));
        $expire = dformat($locktime + $conf['locktime']);
        $min    = round(($conf['locktime'] - (time() - $locktime) )/60);

        // print intro
        print p_locale_xhtml('locked');

        print '<ul>';
        print '<li><div class="li"><strong>'.$lang['lockedby'].'</strong> '.editorinfo($INFO['locked']).'</div></li>';
        print '<li><div class="li"><strong>'.$lang['lockexpire'].'</strong> '.$expire.' ('.$min.' min)</div></li>';
        print '</ul>'.DOKU_LF;
    }

}
