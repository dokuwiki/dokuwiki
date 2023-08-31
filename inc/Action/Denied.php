<?php

namespace dokuwiki\Action;

use dokuwiki\Ui\Login;
use dokuwiki\Extension\Event;
use dokuwiki\Ui;

/**
 * Class Denied
 *
 * Show the access denied screen
 *
 * @package dokuwiki\Action
 */
class Denied extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        $this->showBanner();

        $data = null;
        $event = new Event('ACTION_DENIED_TPLCONTENT', $data);
        if ($event->advise_before()) {
            global $INPUT;
            if (empty($INPUT->server->str('REMOTE_USER')) && actionOK('login')) {
                (new Login())->show();
            }
        }
        $event->advise_after();
    }

    /**
     * Display error on denied pages
     *
     * @author   Andreas Gohr <andi@splitbrain.org>
     *
     * @return void
     */
    public function showBanner()
    {
        // print intro
        echo p_locale_xhtml('denied');
    }
}
