<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:49 AM
 */

namespace dokuwiki\Action;

class Media extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    public function tplContent() {
        tpl_media();
    }

}
