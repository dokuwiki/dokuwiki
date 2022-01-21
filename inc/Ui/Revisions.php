<?php

namespace dokuwiki\Ui;

use dokuwiki\Ui\PageRevisions;
use dokuwiki\Ui\MediaRevisions;

/**
 * DokuWiki Generic Revision list
 *
 * @package dokuwiki\Ui
 */
class Revisions extends Ui
{
    /* @var string */
    protected $mode;   // page or media

    /* @var RevisionsInterface */
    protected $RevisonList;

    /**
     * Revisions Ui constructor
     *
     * @param string $id  page id or media id
     */
    public function __construct($id)
    {
        if (empty($id)) {
            $this->mode = 'media';
        } else {
            $this->mode = strrpos($id, '.') ? 'media' : 'page';
        }

        // revision list switching strategy
        $this->RevisonList = $this->getRevisionsInterface($id);
    }

    /**
     * Get instance of revisions interface
     *
     * @param string $id  page id or media id
     */
    protected function getRevisionsInterface($id)
    {
        switch ($this->mode) {
            case 'page' : return new PageRevisions($id);
            case 'media': return new MediaRevisions($id);
        }
    }

    /**
     * Return id of page or media file
     * @return string
     */
    public function getId()
    {
        return $this->RevisonList->getId();
    }

    /**
     * Display revision list
     */
    public function show()
    {
        $this->RevisonList->show();
    }

}
