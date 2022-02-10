<?php

namespace dokuwiki\Ui;

/**
 * DokuWiki Backlinks Interface
 *
 * @package dokuwiki\Ui
 */
class Backlinks extends Ui
{
    /**
     * Display backlinks
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     * @author   Michael Klier <chi@chimeric.de>
     *
     * @return void
     */
    public function show()
    {
        global $ID;
        global $lang;

        // print intro
        print p_locale_xhtml('backlinks');

        $data = ft_backlinks($ID);

        if (!empty($data)) {
            print '<ul class="idx">';
            foreach ($data as $blink) {
                print '<li><div class="li">';
                print html_wikilink(':'.$blink,useHeading('navigation') ? null : $blink);
                print '</div></li>';
            }
            print '</ul>';
        } else {
            print '<div class="level1"><p>'. $lang['nothingfound'] .'</p></div>';
        }
    }

}
