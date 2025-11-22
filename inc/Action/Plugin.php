<?php

namespace easywiki\Action;

use easywiki\Extension\Event;

/**
 * Class Plugin
 *
 * Used to run action plugins
 *
 * @package easywiki\Action
 */
class Plugin extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    /**
     * Outputs nothing but a warning unless an action plugin overwrites it
     *
     * @inheritdoc
     * @triggers TPL_ACT_UNKNOWN
     */
    public function tplContent()
    {
        $evt = new Event('TPL_ACT_UNKNOWN', $this->actionname);
        if ($evt->advise_before()) {
            msg('Failed to handle action: ' . hsc($this->actionname), -1);
        }
        $evt->advise_after();
    }
}
