<?php

namespace dokuwiki\Action;

/**
 * Class Source
 *
 * Show the source of a page
 *
 * @package dokuwiki\Action
 */
class Source extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent() {
        html_edit(); // FIXME is this correct? Should we split it off completely?
    }

}
