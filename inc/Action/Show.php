<?php

/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 4:32 PM
 */

namespace easywiki\Action;

use easywiki\Ui\PageView;
use easywiki\Ui;

/**
 * Class Show
 *
 * The default action of showing a page
 *
 * @package easywiki\Action
 */
class Show extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $ID;
        unlock($ID);
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new PageView())->show();
    }
}
