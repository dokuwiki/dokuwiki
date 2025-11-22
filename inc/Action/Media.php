<?php

namespace easywiki\Action;

/**
 * Class Media
 *
 * The full screen media manager
 *
 * @package easywiki\Action
 */
class Media extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent()
    {
        tpl_media();
    }
}
