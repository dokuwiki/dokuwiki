<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:31 AM
 */

namespace dokuwiki\Action;

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
