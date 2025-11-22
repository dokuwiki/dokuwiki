<?php

namespace easywiki\Ui;

/**
 * Class Ui
 *
 * Abstract base class for all EasyWiki screens
 *
 * @package easywiki\Ui
 */
abstract class Ui
{
    /**
     * Display the UI element
     *
     * @return void
     */
    abstract public function show();
}
