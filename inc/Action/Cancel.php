<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 10:13 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

class Cancel extends AbstractAliasAction {

    public function preProcess() {
        throw new ActionAbort();
    }

}
