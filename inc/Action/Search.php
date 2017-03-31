<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Search
 *
 * Search for pages and content
 *
 * @package dokuwiki\Action
 */
class Search extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /**
     * we only search if a search word was given
     *
     * @inheritdoc
     */
    public function checkPermissions() {
        parent::checkPermissions();
        global $QUERY;
        $s = cleanID($QUERY);
        if($s === '') throw new ActionAbort();
    }

    /** @inheritdoc */
    public function tplContent() {
        html_search();
    }
}
