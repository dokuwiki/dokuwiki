<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:44 AM
 */

namespace dokuwiki\Action;

class Source extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    public function tplContent() {
        html_edit(); // FIXME is this correct? Should we split it off completely?
    }

}
