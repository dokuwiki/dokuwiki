<?php

namespace dokuwiki\Ui;

/**
 * DokuWiki Revision List Interface
 *
 * @package dokuwiki\Ui
 */
interface RevisionsInterface
{
    /**
     * Return id of page or media file
     * @return string
     */
    public function getId();

    /**
     * Display revision list
     */
    public function show();
}
