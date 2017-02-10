<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 4:32 PM
 */

namespace dokuwiki\Action;

class Show extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    public function tplContent() {
        html_show();
    }

}
