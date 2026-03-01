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
     * @return void
     * @author   Michael Klier <chi@chimeric.de>
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     */
    public function show()
    {
        global $ID;
        global $lang;

        // print intro
        echo p_locale_xhtml('backlinks');

        $data = ft_backlinks($ID);

        if (!empty($data)) {
            echo '<ul class="idx">';
            foreach ($data as $blink) {
                echo '<li><div class="li">';
                echo html_wikilink(':' . $blink, useHeading('navigation') ? null : $blink);
                echo '</div></li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="level1"><p>' . $lang['nothingfound'] . '</p></div>';
        }
    }
}
