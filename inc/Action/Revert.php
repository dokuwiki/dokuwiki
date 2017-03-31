<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionException;

/**
 * Class Revert
 *
 * Quick revert to an old revision
 *
 * @package dokuwiki\Action
 */
class Revert extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        global $INFO;
        if($INFO['ismanager']) {
            return AUTH_EDIT;
        } else {
            return AUTH_ADMIN;
        }
    }

    /**
     *
     * @inheritdoc
     * @throws ActionAbort
     * @throws ActionException
     * @todo check for writability of the current page ($INFO might do it wrong and check the attic version)
     */
    public function preProcess() {
        if(!checkSecurityToken()) throw new ActionException();

        global $ID;
        global $REV;
        global $lang;
        global $INPUT;

        // when no revision is given, delete current one
        // FIXME this feature is not exposed in the GUI currently
        $text = '';
        $sum = $lang['deleted'];
        if($REV) {
            $text = rawWiki($ID, $REV);
            if(!$text) throw new ActionException(); //something went wrong
            $sum = sprintf($lang['restored'], dformat($REV));
        }

        // spam check
        if(checkwordblock($text)) {
            msg($lang['wordblock'], -1);
            throw new ActionException('edit');
        }

        saveWikiText($ID, $text, $sum, false);
        msg($sum, 1);

        //delete any draft
        act_draftdel('fixme'); // FIXME replace this utility function
        //session_write_close(); // FIXME sessions should be close somewhere higher up, maybe ActionRouter

        // when done, show current page
        $INPUT->server->set('REQUEST_METHOD', 'post'); //should force a redirect // FIXME should we have a RedirectException?
        $REV = '';

        throw new ActionAbort();
    }

}
