<?php

namespace dokuwiki\Ui;

use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Denied Insterface
 *
 * @package dokuwiki\Ui
 */
class Denied extends Ui
{
    /**
     * Show denied page content
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function show()
    {
        // print intro
        print p_locale_xhtml('denied');

        if (empty($_SERVER['REMOTE_USER']) && actionOK('login')) {
            (new Login)->show();
        }
    }

}
