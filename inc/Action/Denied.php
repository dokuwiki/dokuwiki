<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 4:51 PM
 */

namespace dokuwiki\Action;

class Denied extends AbstractAclAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    public function tplContent() {
        html_denied();
    }

}
