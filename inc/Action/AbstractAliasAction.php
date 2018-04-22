<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\FatalException;

/**
 * Class AbstractAliasAction
 *
 * An action that is an alias for another action. Skips the minimumPermission check
 *
 * Be sure to implement preProcess() and throw an ActionAbort exception
 * with the proper action.
 *
 * @package dokuwiki\Action
 */
abstract class AbstractAliasAction extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    public function preProcess() {
        throw new FatalException('Alias Actions need to implement preProcess to load the aliased action');
    }

}
