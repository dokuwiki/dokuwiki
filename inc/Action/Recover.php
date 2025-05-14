<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Recover
 *
 * Recover a draft
 *
 * @package dokuwiki\Action
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
