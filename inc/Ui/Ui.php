<?php
namespace dokuwiki\Ui;

/**
 * Class Ui
 *
 * Abstract base class for all DokuWiki screens
 *
 * @package dokuwiki\Ui
 */
abstract class Ui {

    /**
     * Display the UI element
     *
     * @return void
     */
    abstract public function show();

}
