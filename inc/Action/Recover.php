<?php

namespace easywiki\Action;

use easywiki\Action\Exception\ActionAbort;

/**
 * Class Recover
 *
 * Recover a draft
 *
 * @package easywiki\Action
 */
class Recover extends AbstractAliasAction
{
    /**
     * @inheritdoc
     * @throws ActionAbort
     */
    public function preProcess()
    {
        throw new ActionAbort('edit');
    }
}
