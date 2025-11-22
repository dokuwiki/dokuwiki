<?php

namespace easywiki\Action;

use easywiki\Action\Exception\ActionAbort;

/**
 * Class Cancel
 *
 * Alias for show. Aborts editing
 *
 * @package easywiki\Action
 */
class Cancel extends AbstractAliasAction
{
    /**
     * @inheritdoc
     * @throws ActionAbort
     */
    public function preProcess()
    {
        global $ID;
        unlock($ID);

        // continue with draftdel -> redirect -> show
        throw new ActionAbort('draftdel');
    }
}
