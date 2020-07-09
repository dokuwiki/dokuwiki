<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Locked Insterface
 *
 * @package dokuwiki\Ui
 */
class Locked extends Ui
{
    /**
     * Display error on locked pages
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function show()
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
