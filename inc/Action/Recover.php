<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 10:26 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

class Recover extends AbstractAliasAction {

    public function preProcess() {
        throw new ActionAbort('edit');
    }

}
