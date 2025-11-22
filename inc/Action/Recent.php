<?php

namespace easywiki\Action;

use easywiki\Ui;

/**
 * Class Recent
 *
 * The recent changes view
 *
 * @package easywiki\Action
 */
class Recent extends AbstractAction
{
    /** @var string what type of changes to show */
    protected $showType = 'both';

    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $INPUT;
        $show_changes = $INPUT->str('show_changes');
        if (!empty($show_changes)) {
            set_doku_pref('show_changes', $show_changes);
            $this->showType = $show_changes;
        } else {
            $this->showType = get_doku_pref('show_changes', 'both');
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        global $INPUT;
        (new Ui\Recent($INPUT->extract('first')->int('first'), $this->showType))->show();
    }
}
