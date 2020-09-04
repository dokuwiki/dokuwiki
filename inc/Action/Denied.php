<?php

namespace dokuwiki\Action;

use dokuwiki\Ui;

/**
 * Class Denied
 *
 * Show the access denied screen
 *
 * @package dokuwiki\Action
 */
class Denied extends AbstractAclAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        global $INPUT;
        $this->showBanner();
        if (empty($INPUT->server->str('REMOTE_USER')) && actionOK('login')) {
            (new Ui\Login)->show();
        }
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
        print p_locale_xhtml('denied');
    }

}
