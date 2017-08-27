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
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function tplContent() {
        global $IDX;
        html_index($IDX);
    }

}
