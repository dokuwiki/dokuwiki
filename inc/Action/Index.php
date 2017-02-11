<?php

namespace dokuwiki\Action;

/**
 * Class Index
 *
 * Show the human readable sitemap. Do not confuse with Sitemap
 *
 * @package dokuwiki\Action
 */
class Index extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    public function tplContent() {
        global $IDX;
        html_index($IDX);
    }

}
