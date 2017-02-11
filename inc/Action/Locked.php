<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:43 AM
 */

namespace dokuwiki\Action;

class Locked extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    public function tplContent() {
        html_locked();
    }

}
